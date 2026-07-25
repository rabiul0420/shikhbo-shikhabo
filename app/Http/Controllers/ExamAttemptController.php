<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    public function myResults(Request $request): View
    {
        $attempts = ExamAttempt::query()
            ->with(['exam.academicClass', 'exam.subject', 'exam.chapter'])
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->latest()
            ->get();

        return view('exam-attempts.index', compact('attempts'));
    }

    public function allResults(Request $request, Exam $exam): View
    {
        $exam->load(['academicClass', 'subject', 'chapter']);
        $this->authorizeExamResultsForUser($request, $exam);

        $attempts = ExamAttempt::query()
            ->with('user')
            ->where('exam_id', $exam->id)
            ->orderByDesc('score')
            ->latest('submitted_at')
            ->get();

        $highestAttempt = $attempts->sortByDesc('score')->first();
        $attemptPositions = $this->attemptPositions($attempts);
        $myAttempt = $attempts
            ->where('user_id', auth()->id())
            ->sortByDesc('score')
            ->first();

        return view('exam-attempts.all-results', compact(
            'exam',
            'attempts',
            'highestAttempt',
            'attemptPositions',
            'myAttempt'
        ));
    }

    public function show(string $examSlug): View
    {
        $exam = Exam::findBySlug($examSlug) ?? abort(404);

        $exam->load(['academicClass', 'subject', 'chapter']);
        $this->authorizeExamForUser($exam);

        $canViewQuestions = auth()->check() && $exam->scheduleStatus() !== 'upcoming';

        if ($canViewQuestions) {
            $exam->load('questions.options');
        }

        $attemptStartedAt = null;
        $existingAttempt = null;
        $user = request()->user();
        $questionCount = $exam->questions()->count();
        $totalMarks = (int) $exam->questions()->sum('marks');

        if ($user && ! $user->is_admin) {
            $existingAttempt = $this->existingAttemptForUser($exam, $user->id);
        }

        if ($user && ! $existingAttempt && $exam->isRunning() && $exam->duration_minutes) {
            $sessionKey = $this->examSessionKey($exam);
            $attemptStartedAt = request()->session()->get($sessionKey);

            if (! $attemptStartedAt) {
                $attemptStartedAt = now()->toIso8601String();
                request()->session()->put($sessionKey, $attemptStartedAt);
            }
        }

        return view('exams.show', compact(
            'exam',
            'attemptStartedAt',
            'existingAttempt',
            'questionCount',
            'totalMarks',
            'canViewQuestions'
        ));
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $exam->load('questions.options');
        $this->authorizeExamForUser($exam);

        if (! $exam->isRunning()) {
            throw ValidationException::withMessages([
                'exam' => 'This exam is not running now.',
            ]);
        }

        if (! $request->user()->is_admin && $existingAttempt = $this->existingAttemptForUser($exam, $request->user()->id)) {
            $request->session()->forget($this->examSessionKey($exam));

            return redirect()
                ->route('exam-attempts.result', $existingAttempt)
                ->with('status', 'You have already participated in this exam.');
        }

        $attemptStartedAt = $this->attemptStartedAt($request, $exam);

        if ($exam->duration_minutes && ! $attemptStartedAt) {
            throw ValidationException::withMessages([
                'exam' => 'Please open the exam page before submitting answers.',
            ]);
        }

        $submissionGraceSeconds = $request->boolean('auto_submitted') ? 120 : 5;

        if ($exam->duration_minutes && $attemptStartedAt->copy()->addMinutes($exam->duration_minutes)->addSeconds($submissionGraceSeconds)->isPast()) {
            throw ValidationException::withMessages([
                'exam' => 'The exam time is over.',
            ]);
        }

        try {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'user_id' => $request->user()->id,
                'status' => 'graded',
                'score' => 0,
                'total_marks' => $exam->questions->sum('marks'),
                'started_at' => $attemptStartedAt ?? now(),
                'submitted_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if (! $request->user()->is_admin && $existingAttempt = $this->existingAttemptForUser($exam, $request->user()->id)) {
                $request->session()->forget($this->examSessionKey($exam));

                return redirect()
                    ->route('exam-attempts.result', $existingAttempt)
                    ->with('status', 'You have already participated in this exam.');
            }

            throw $exception;
        }

        $score = 0;
        $submittedAnswers = $request->input('answers', []);

        foreach ($exam->questions as $question) {
            $selectedIds = collect($submittedAnswers[$question->id] ?? [])
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $correctIds = $question->options
                ->where('is_correct', true)
                ->pluck('id')
                ->sort()
                ->values();

            $isCorrect = $selectedIds->isNotEmpty() && $selectedIds->all() === $correctIds->all();
            $marksAwarded = $isCorrect ? $question->marks : 0;
            $score += $marksAwarded;

            if ($selectedIds->isEmpty()) {
                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'question_option_id' => null,
                    'is_correct' => false,
                    'marks_awarded' => 0,
                ]);

                continue;
            }

            foreach ($selectedIds as $optionId) {
                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'question_option_id' => $optionId,
                    'is_correct' => $isCorrect,
                    'marks_awarded' => $marksAwarded,
                ]);
            }
        }

        $attempt->update(['score' => $score]);
        $request->session()->forget($this->examSessionKey($exam));

        return redirect()->route('exam-attempts.result', $attempt);
    }

    public function result(Request $request, ExamAttempt $attempt): View
    {
        abort_unless(
            $request->user()->is_admin || (int) $attempt->user_id === (int) $request->user()->id,
            403
        );

        $attempt->load(['exam.questions.options', 'answers.option']);

        $examAttempts = ExamAttempt::query()
            ->where('exam_id', $attempt->exam_id)
            ->orderByDesc('score')
            ->latest('submitted_at')
            ->get();
        $attemptPositions = $this->attemptPositions($examAttempts);
        $attemptPosition = $attemptPositions[$attempt->id] ?? null;
        $participantCount = $examAttempts->count();

        return view('exam-attempts.result', compact(
            'attempt',
            'attemptPosition',
            'participantCount'
        ));
    }

    private function attemptPositions($attempts): array
    {
        $positions = [];
        $currentPosition = 0;
        $previousScore = null;

        foreach ($attempts->sortByDesc('score')->values() as $attempt) {
            if ($previousScore === null || (int) $attempt->score !== (int) $previousScore) {
                $currentPosition++;
            }

            $positions[$attempt->id] = $currentPosition;
            $previousScore = $attempt->score;
        }

        return $positions;
    }

    private function authorizeExamForUser(Exam $exam): void
    {
        $user = request()->user();

        if (! $user) {
            return;
        }

        abort_unless(
            $user->is_admin || (int) $exam->academic_class_id === (int) $user->academic_class_id,
            403
        );
    }

    private function authorizeExamResultsForUser(Request $request, Exam $exam): void
    {
        $user = $request->user();

        abort_unless(
            $user->is_admin
                || (int) $exam->academic_class_id === (int) $user->academic_class_id
                || ExamAttempt::query()
                    ->where('exam_id', $exam->id)
                    ->where('user_id', $user->id)
                    ->exists(),
            403
        );
    }

    private function attemptStartedAt(Request $request, Exam $exam): ?\Illuminate\Support\Carbon
    {
        $startedAt = $request->session()->get($this->examSessionKey($exam));

        return $startedAt ? \Illuminate\Support\Carbon::parse($startedAt) : null;
    }

    private function existingAttemptForUser(Exam $exam, int $userId): ?ExamAttempt
    {
        return ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->latest()
            ->first();
    }

    private function examSessionKey(Exam $exam): string
    {
        return 'exam_started_at.' . $exam->id;
    }
}

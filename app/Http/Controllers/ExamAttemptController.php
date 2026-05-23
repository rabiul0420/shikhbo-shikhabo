<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function allResults(Exam $exam): View
    {
        $exam->load(['academicClass', 'subject', 'chapter']);

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

    public function show(Exam $exam): View
    {
        $exam->load(['academicClass', 'subject', 'chapter', 'questions.options']);

        return view('exams.show', compact('exam'));
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $exam->load('questions.options');

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $request->user()->id,
            'status' => 'graded',
            'score' => 0,
            'total_marks' => $exam->questions->sum('marks'),
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

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

        return redirect()->route('exam-attempts.result', $attempt);
    }

    public function result(Request $request, ExamAttempt $attempt): View
    {
        abort_unless(
            $request->user()->is_admin || (int) $attempt->user_id === (int) $request->user()->id,
            403
        );

        $attempt->load(['exam.questions.options', 'answers.option']);

        return view('exam-attempts.result', compact('attempt'));
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
}

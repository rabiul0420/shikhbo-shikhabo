<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\CustomExam;
use App\Models\CustomExamAttempt;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomExamController extends Controller
{
    public function create(Request $request): View
    {
        $subjects = Subject::query()->orderBy('name')->get();
        $customExams = CustomExam::query()
            ->with(['subject', 'chapter', 'attempts' => fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->latest('submitted_at')
                ->latest()])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(8)
            ->get();

        return view('custom-exams.create', compact('subjects', 'customExams'));
    }

    public function chapterOptions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $chapters = Chapter::query()
            ->where('academic_class_id', $request->user()->academic_class_id)
            ->where('subject_id', $data['subject_id'])
            ->orderBy('chapter_no')
            ->orderBy('name')
            ->get(['id', 'chapter_no', 'name']);

        return response()->json([
            'chapters' => $chapters->map(fn (Chapter $chapter) => [
                'id' => $chapter->id,
                'name' => $chapter->display_name,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->academic_class_id, 403);

        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'question_count' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::query()
            ->whereKey($data['chapter_id'])
            ->where('academic_class_id', $user->academic_class_id)
            ->where('subject_id', $subject->id)
            ->first();

        if (! $chapter) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Selected subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);
        }

        $questions = Question::query()
            ->where('academic_class_id', $user->academic_class_id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit((int) $data['question_count'])
            ->get();

        if ($questions->count() < (int) $data['question_count']) {
            throw ValidationException::withMessages([
                'question_count' => 'Selected subject and oddhay / chapter e eto question available nei.',
            ]);
        }

        $customExam = DB::transaction(function () use ($user, $subject, $chapter, $questions, $data) {
            $customExam = CustomExam::create([
                'user_id' => $user->id,
                'academic_class_id' => $user->academic_class_id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'title' => $subject->name . ' - ' . $chapter->display_name . ' Custom Exam',
                'question_count' => (int) $data['question_count'],
                'total_marks' => (int) $questions->sum('marks'),
            ]);

            $customExam->questions()->sync($questions->pluck('id'));

            return $customExam;
        });

        return redirect()->route('custom-exams.show', $customExam);
    }

    public function show(Request $request, CustomExam $customExam): View
    {
        $this->authorizeCustomExam($request, $customExam);

        $customExam->load(['academicClass', 'subject', 'chapter', 'questions.options']);
        $existingAttempt = $customExam->attempts()
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->latest()
            ->first();

        return view('custom-exams.show', compact('customExam', 'existingAttempt'));
    }

    public function submit(Request $request, CustomExam $customExam): RedirectResponse
    {
        $this->authorizeCustomExam($request, $customExam);

        if ($existingAttempt = $customExam->attempts()->where('user_id', $request->user()->id)->first()) {
            return redirect()->route('custom-exam-attempts.result', $existingAttempt);
        }

        $customExam->load('questions.options');
        $score = 0;
        $submittedAnswers = $request->input('answers', []);

        $attempt = DB::transaction(function () use ($request, $customExam, $submittedAnswers, &$score) {
            $attempt = CustomExamAttempt::create([
                'custom_exam_id' => $customExam->id,
                'user_id' => $request->user()->id,
                'status' => 'graded',
                'score' => 0,
                'total_marks' => $customExam->total_marks,
                'started_at' => now(),
                'submitted_at' => now(),
            ]);

            foreach ($customExam->questions as $question) {
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

            return $attempt;
        });

        return redirect()->route('custom-exam-attempts.result', $attempt);
    }

    public function results(Request $request): View
    {
        $attempts = CustomExamAttempt::query()
            ->with(['customExam.subject', 'customExam.chapter'])
            ->where('user_id', $request->user()->id)
            ->latest('submitted_at')
            ->latest()
            ->get();

        return view('custom-exams.results', compact('attempts'));
    }

    public function result(Request $request, CustomExamAttempt $attempt): View
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()->id || $request->user()->is_admin, 403);

        $attempt->load(['customExam.questions.options', 'customExam.subject', 'customExam.chapter', 'answers.option']);

        return view('custom-exams.result', compact('attempt'));
    }

    private function authorizeCustomExam(Request $request, CustomExam $customExam): void
    {
        abort_unless(
            (int) $customExam->user_id === (int) $request->user()->id || $request->user()->is_admin,
            403
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
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

    public function result(ExamAttempt $attempt): View
    {
        abort_unless(
            request()->user()->is_admin || $attempt->user_id === request()->user()->id,
            403
        );

        $attempt->load(['exam.questions.options', 'answers.option']);

        return view('exam-attempts.result', compact('attempt'));
    }
}

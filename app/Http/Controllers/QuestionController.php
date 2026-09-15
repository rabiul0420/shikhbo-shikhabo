<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->with('academicClasses')->orderBy('name')->get();

        return view('admin.questions.create', compact('classes', 'subjects'));
    }

    public function storeStandalone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'question_text' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['required', 'integer'],
        ]);

        $options = collect($data['options'])
            ->map(fn ($option) => is_string($option) ? trim($option) : $option)
            ->filter(fn ($option) => $option !== null && $option !== '');

        if ($options->count() < 2) {
            throw ValidationException::withMessages([
                'options' => 'Please provide at least two answer options.',
            ]);
        }

        $correctOption = (int) $data['correct_option'];

        if (! $options->has($correctOption)) {
            throw ValidationException::withMessages([
                'correct_option' => 'Please choose a filled option as the correct answer.',
            ]);
        }

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::query()
            ->whereKey($data['chapter_id'])
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->first();

        if (! $chapter) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Selected class and subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);
        }

        $question = Question::create([
            'created_by' => $request->user()->id,
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => $data['question_text'],
            'type' => 'single_choice',
            'marks' => 1,
            'explanation' => null,
            'sort_order' => Question::query()
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->count() + 1,
            'is_active' => true,
        ]);

        foreach ($options as $index => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => $index === $correctOption,
                'sort_order' => $index + 1,
            ]);
        }

        return back()->with('status', 'Question added.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'bulk_questions' => ['required', 'string'],
        ]);

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = $this->validChapter($academicClass, $subject, (int) $data['chapter_id']);

        if (! $chapter) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Selected class and subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);
        }

        $parsedQuestions = $this->parseBulkQuestions($data['bulk_questions']);
        $existingQuestionTexts = Question::query()
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->pluck('question_text')
            ->map(fn ($text) => $this->normalizeQuestionText($text))
            ->flip();
        $skippedCount = 0;

        $questionsToStore = $parsedQuestions->filter(function (array $parsedQuestion) use ($existingQuestionTexts, &$skippedCount) {
            $questionText = $this->normalizeQuestionText($parsedQuestion['question_text']);

            if ($existingQuestionTexts->has($questionText)) {
                $skippedCount++;

                return false;
            }

            $existingQuestionTexts->put($questionText, true);

            return true;
        })->values();

        DB::transaction(function () use ($request, $academicClass, $subject, $chapter, $questionsToStore) {
            $sortOrder = Question::query()
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->count();

            foreach ($questionsToStore as $parsedQuestion) {
                $question = Question::create([
                    'created_by' => $request->user()->id,
                    'academic_class_id' => $academicClass->id,
                    'subject_id' => $subject->id,
                    'chapter_id' => $chapter->id,
                    'question_text' => $parsedQuestion['question_text'],
                    'type' => 'single_choice',
                    'marks' => 1,
                    'explanation' => null,
                    'sort_order' => ++$sortOrder,
                    'is_active' => true,
                ]);

                foreach ($parsedQuestion['options'] as $index => $optionText) {
                    $question->options()->create([
                        'option_text' => $optionText,
                        'is_correct' => $index === $parsedQuestion['correct_option'],
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });

        $status = $questionsToStore->count() . ' questions added.';

        if ($skippedCount > 0) {
            $status .= ' ' . $skippedCount . ' skipped.';
        }

        return back()->with('status', $status);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        abort_unless($question->canBeManagedBy($request->user()), 403);
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:1000'],
            'correct_option' => ['required', 'integer'],
        ]);

        $options = collect($data['options'])
            ->map(fn ($option) => is_string($option) ? trim($option) : $option)
            ->filter(fn ($option) => $option !== null && $option !== '');

        if ($options->count() < 2) {
            throw ValidationException::withMessages([
                'options' => 'Please provide at least two answer options.',
            ]);
        }

        $correctOption = (int) $data['correct_option'];

        if (! $options->has($correctOption)) {
            throw ValidationException::withMessages([
                'correct_option' => 'Please choose a filled option as the correct answer.',
            ]);
        }

        $question->update([
            'question_text' => $data['question_text'],
        ]);

        $question->options()->delete();

        foreach ($options as $index => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => $index === $correctOption,
                'sort_order' => $index + 1,
            ]);
        }

        return back()->with('status', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        abort_unless($question->canBeManagedBy(request()->user()), 403);
        $question->delete();

        return back()->with('status', 'Question deleted.');
    }

    private function validChapter(AcademicClass $academicClass, Subject $subject, int $chapterId): ?Chapter
    {
        return Chapter::query()
            ->whereKey($chapterId)
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->first();
    }

    private function parseBulkQuestions(string $bulkQuestions): Collection
    {
        $lines = preg_split('/\R/u', trim($bulkQuestions));
        $questions = collect();
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^(?:\d+|[\x{09E6}-\x{09EF}]+)[\.\)]\s*(.+)$/u', $line, $matches)) {
                if ($current !== null) {
                    $questions->push($current);
                }

                $current = [
                    'question_text' => trim($matches[1]),
                    'options' => [],
                    'correct_option' => null,
                ];

                continue;
            }

            if ($current === null) {
                throw ValidationException::withMessages([
                    'bulk_questions' => 'প্রথমে প্রশ্ন নম্বর দিয়ে শুরু করুন, যেমন: 1. প্রশ্ন',
                ]);
            }

            if (preg_match('/^([A-Da-d])[\)\.]\s*(.+)$/u', $line, $matches)) {
                $current['options'][strtoupper($matches[1])] = trim($matches[2]);
                continue;
            }

            if (preg_match('/^[^:：]{1,20}[:：]\s*([A-Da-d])/u', $line, $matches)) {
                $current['correct_option'] = strtoupper($matches[1]);
                continue;
            }

            if (empty($current['options'])) {
                $current['question_text'] .= ' ' . $line;
            }
        }

        if ($current !== null) {
            $questions->push($current);
        }

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_questions' => 'কমপক্ষে একটি প্রশ্ন দিন।',
            ]);
        }

        return $questions->values()->map(function (array $question, int $index) {
            $optionLetters = collect(['A', 'B', 'C', 'D'])
                ->filter(fn ($letter) => isset($question['options'][$letter]) && $question['options'][$letter] !== '')
                ->values();
            $options = $optionLetters->map(fn ($letter) => $question['options'][$letter]);

            if ($options->count() < 2) {
                throw ValidationException::withMessages([
                    'bulk_questions' => 'Question ' . ($index + 1) . ' এ কমপক্ষে দুটি option থাকতে হবে।',
                ]);
            }

            if (! $question['correct_option'] || ! isset($question['options'][$question['correct_option']])) {
                throw ValidationException::withMessages([
                    'bulk_questions' => 'Question ' . ($index + 1) . ' এর সঠিক উত্তর A/B/C/D দিয়ে লিখুন।',
                ]);
            }

            return [
                'question_text' => $question['question_text'],
                'options' => $options,
                'correct_option' => $optionLetters->search($question['correct_option']),
            ];
        });
    }

    private function normalizeQuestionText(string $questionText): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $questionText));
    }
}

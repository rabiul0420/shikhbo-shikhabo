<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.questions.create', compact('classes', 'subjects'));
    }

    public function storeStandalone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
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

    public function update(Request $request, Question $question): RedirectResponse
    {
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
        $question->delete();

        return back()->with('status', 'Question deleted.');
    }
}

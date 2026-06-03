<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::query()
            ->with(['academicClass', 'subject', 'chapter', 'questions'])
            ->withCount('questions')
            ->latest()
            ->get();
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $questions = Question::query()
            ->with(['academicClass', 'subject', 'chapter'])
            ->latest()
            ->get();

        return view('admin.exams.index', compact('exams', 'classes', 'subjects', 'questions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'question_selection_mode' => $request->input('question_selection_mode', 'manual'),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'first_prize' => ['nullable', 'string', 'max:255'],
            'second_prize' => ['nullable', 'string', 'max:255'],
            'third_prize' => ['nullable', 'string', 'max:255'],
            'question_selection_mode' => ['required', Rule::in(['manual', 'random'])],
            'random_question_count' => ['nullable', 'required_if:question_selection_mode,random', 'integer', 'min:1', 'max:500'],
            'question_ids' => ['nullable', 'required_if:question_selection_mode,manual', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

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

        if ($data['question_selection_mode'] === 'random') {
            $questionIds = $this->randomQuestionIds($data, $academicClass, $subject, $chapter);
        } else {
            $questionIds = Question::query()
                ->whereIn('id', $data['question_ids'])
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->pluck('id');

            if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
                throw ValidationException::withMessages([
                    'question_ids' => 'Selected class, subject এবং oddhay / chapter অনুযায়ী question select করুন।',
                ]);
            }

        }

        $exam = Exam::create([
            'created_by' => $request->user()->id,
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'duration_minutes' => $data['duration_minutes'],
            'first_prize' => $data['first_prize'] ?? null,
            'second_prize' => $data['second_prize'] ?? null,
            'third_prize' => $data['third_prize'] ?? null,
        ]);

        $exam->questions()->sync($questionIds);

        return back()->with('status', 'Exam added.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('status', 'Exam deleted.');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $request->merge([
            'question_selection_mode' => $request->input('question_selection_mode', 'manual'),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'first_prize' => ['nullable', 'string', 'max:255'],
            'second_prize' => ['nullable', 'string', 'max:255'],
            'third_prize' => ['nullable', 'string', 'max:255'],
            'question_selection_mode' => ['required', Rule::in(['manual', 'random'])],
            'random_question_count' => ['nullable', 'required_if:question_selection_mode,random', 'integer', 'min:1', 'max:500'],
            'question_ids' => ['nullable', 'required_if:question_selection_mode,manual', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

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

        if ($data['question_selection_mode'] === 'random') {
            $questionIds = $this->randomQuestionIds($data, $academicClass, $subject, $chapter);
        } else {
            $questionIds = Question::query()
                ->whereIn('id', $data['question_ids'])
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->pluck('id');

            if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
                throw ValidationException::withMessages([
                    'question_ids' => 'Selected class, subject এবং oddhay / chapter অনুযায়ী question select করুন।',
                ]);
            }

        }

        $exam->update([
            'title' => $data['title'],
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'duration_minutes' => $data['duration_minutes'],
            'first_prize' => $data['first_prize'] ?? null,
            'second_prize' => $data['second_prize'] ?? null,
            'third_prize' => $data['third_prize'] ?? null,
        ]);

        $exam->questions()->sync($questionIds);

        return back()->with('status', 'Exam updated.');
    }

    private function randomQuestionIds(array $data, AcademicClass $academicClass, Subject $subject, Chapter $chapter): Collection
    {
        $questionIds = Question::query()
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->inRandomOrder()
            ->limit((int) $data['random_question_count'])
            ->pluck('id');

        if ($questionIds->count() !== (int) $data['random_question_count']) {
            throw ValidationException::withMessages([
                'random_question_count' => 'Selected class, subject and oddhay / chapter e eto question available nei.',
            ]);
        }

        return $questionIds;
    }
}

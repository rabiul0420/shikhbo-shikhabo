<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $lessons = Chapter::query()->with(['academicClass', 'subject'])->orderBy('name')->get();
        $questions = Question::query()
            ->with(['academicClass', 'subject', 'chapter'])
            ->latest()
            ->get();

        return view('admin.exams.index', compact('exams', 'classes', 'subjects', 'lessons', 'questions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::findOrFail($data['chapter_id']);

        if ($chapter->academic_class_id !== $academicClass->id || $chapter->subject_id !== $subject->id) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Please choose a lesson that belongs to the selected class and subject.',
            ]);
        }

        $questionIds = Question::query()
            ->whereIn('id', $data['question_ids'])
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->pluck('id');

        if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
            throw ValidationException::withMessages([
                'question_ids' => 'Please choose only questions from the selected class, subject, and lesson.',
            ]);
        }

        $exam = Exam::create([
            'created_by' => $request->user()->id,
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => $data['title'],
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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::findOrFail($data['chapter_id']);

        if ($chapter->academic_class_id !== $academicClass->id || $chapter->subject_id !== $subject->id) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Please choose a lesson that belongs to the selected class and subject.',
            ]);
        }

        $questionIds = Question::query()
            ->whereIn('id', $data['question_ids'])
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->pluck('id');

        if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
            throw ValidationException::withMessages([
                'question_ids' => 'Please choose only questions from the selected class, subject, and lesson.',
            ]);
        }

        $exam->update([
            'title' => $data['title'],
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
        ]);

        $exam->questions()->sync($questionIds);

        return back()->with('status', 'Exam updated.');
    }
}

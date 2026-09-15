<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicStructureController extends Controller
{
    public function classes(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();

        return view('admin.academic.classes', compact('classes'));
    }

    public function subjects(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()
            ->with('academicClasses')
            ->orderBy('name')
            ->get();

        return view('admin.academic.subjects', compact('classes', 'subjects'));
    }

    public function chapters(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()
            ->with('academicClasses')
            ->orderBy('name')
            ->get();
        $chapters = Chapter::query()
            ->with(['academicClass', 'subject'])
            ->orderBy('chapter_no')
            ->orderBy('name')
            ->get();

        return view('admin.academic.chapters', compact('classes', 'subjects', 'chapters'));
    }

    public function chapterOptions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
        ]);

        $chapters = Chapter::query()
            ->where('academic_class_id', $data['academic_class_id'])
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

    public function storeClass(Request $request): RedirectResponse
    {
        AcademicClass::create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_classes,name'],
        ]) + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Class added.');
    }

    public function updateClass(Request $request, AcademicClass $academicClass): RedirectResponse
    {
        abort_unless($academicClass->canBeManagedBy($request->user()), 403);
        $academicClass->update($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_classes,name,' . $academicClass->id],
        ]));

        return back()->with('status', 'Class updated.');
    }

    public function destroyClass(AcademicClass $academicClass): RedirectResponse
    {
        abort_unless($academicClass->canBeManagedBy(request()->user()), 403);
        $this->ensureOwnedDependents('academic_class_id', $academicClass->id);
        $academicClass->delete();

        return back()->with('status', 'Class deleted.');
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_class_ids' => ['required', 'array', 'min:1'],
            'academic_class_ids.*' => ['exists:academic_classes,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name'),
            ],
        ]);

        $subject = Subject::create(['name' => $data['name'], 'created_by' => $request->user()->id]);
        $subject->academicClasses()->sync($data['academic_class_ids']);

        return back()->with('status', 'Subject added.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        abort_unless($subject->canBeManagedBy($request->user()), 403);
        $data = $request->validate([
            'academic_class_ids' => ['required', 'array', 'min:1'],
            'academic_class_ids.*' => ['exists:academic_classes,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name')->ignore($subject->id),
            ],
        ]);

        $subject->update(['name' => $data['name']]);
        $subject->academicClasses()->sync($data['academic_class_ids']);

        return back()->with('status', 'Subject updated.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        abort_unless($subject->canBeManagedBy(request()->user()), 403);
        $this->ensureOwnedDependents('subject_id', $subject->id);
        $subject->delete();

        return back()->with('status', 'Subject deleted.');
    }

    public function storeChapter(Request $request): RedirectResponse
    {
        Chapter::create($request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_no' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
        ]) + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Oddhay / Chapter added.');
    }

    public function updateChapter(Request $request, Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->canBeManagedBy($request->user()), 403);
        $chapter->update($request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_no' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
        ]));

        return back()->with('status', 'Oddhay / Chapter updated.');
    }

    public function destroyChapter(Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->canBeManagedBy(request()->user()), 403);
        $this->ensureOwnedDependents('chapter_id', $chapter->id);
        $chapter->delete();

        return back()->with('status', 'Oddhay / Chapter deleted.');
    }

    private function ensureOwnedDependents(string $column, int $id): void
    {
        $user = request()->user();
        if ($user->adminRole() !== 'exam_manager') {
            return;
        }

        $notOwned = fn ($query) => $query->whereNull('created_by')->orWhere('created_by', '!=', $user->id);
        $hasOtherExams = \App\Models\Exam::where($column, $id)->where($notOwned)->exists();
        $hasOtherQuestions = \App\Models\Question::where($column, $id)->where($notOwned)->exists();
        $hasOtherChapters = $column !== 'chapter_id'
            && Chapter::where($column, $id)->where($notOwned)->exists();

        abort_if($hasOtherExams || $hasOtherChapters || $hasOtherQuestions, 403, 'This item contains chapters, questions or exams owned by another user.');
    }
}

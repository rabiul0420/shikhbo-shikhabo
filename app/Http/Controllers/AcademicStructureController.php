<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicStructureController extends Controller
{
    public function classes(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();

        return view('admin.academic.classes', compact('classes'));
    }

    public function lessons(): View
    {
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.academic.lessons', compact('subjects'));
    }

    public function chapters(): View
    {
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $chapters = Chapter::query()->with(['academicClass', 'subject'])->orderBy('name')->get();

        return view('admin.academic.chapters', compact('classes', 'subjects', 'chapters'));
    }

    public function storeClass(Request $request): RedirectResponse
    {
        AcademicClass::create($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_classes,name'],
        ]));

        return back()->with('status', 'Class added.');
    }

    public function updateClass(Request $request, AcademicClass $academicClass): RedirectResponse
    {
        $academicClass->update($request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:academic_classes,name,' . $academicClass->id],
        ]));

        return back()->with('status', 'Class updated.');
    }

    public function destroyClass(AcademicClass $academicClass): RedirectResponse
    {
        $academicClass->delete();

        return back()->with('status', 'Class deleted.');
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        Subject::create($request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:subjects,name'],
        ]));

        return back()->with('status', 'Lesson added.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:subjects,name,' . $subject->id],
        ]));

        return back()->with('status', 'Lesson updated.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('status', 'Lesson deleted.');
    }

    public function storeChapter(Request $request): RedirectResponse
    {
        Chapter::create($request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:150'],
        ]));

        return back()->with('status', 'Oddhay / Chapter added.');
    }

    public function updateChapter(Request $request, Chapter $chapter): RedirectResponse
    {
        $chapter->update($request->validate([
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'name' => ['required', 'string', 'max:150'],
        ]));

        return back()->with('status', 'Oddhay / Chapter updated.');
    }

    public function destroyChapter(Chapter $chapter): RedirectResponse
    {
        $chapter->delete();

        return back()->with('status', 'Oddhay / Chapter deleted.');
    }
}

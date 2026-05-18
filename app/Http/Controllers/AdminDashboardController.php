<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $questions = Question::query()
            ->with(['academicClass', 'subject', 'chapter', 'options'])
            ->latest()
            ->get();

        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $lessons = Chapter::query()
            ->with(['academicClass', 'subject'])
            ->orderBy('name')
            ->get();

        return view('admin.index', compact('questions', 'classes', 'subjects', 'lessons'));
    }
}

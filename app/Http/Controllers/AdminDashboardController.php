<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('question_search', ''));

        $questions = Question::query()
            ->with(['academicClass', 'subject', 'chapter', 'options'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question_text', 'like', '%' . $search . '%')
                        ->orWhereHas('academicClass', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('subject', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('chapter', fn ($query) => $query->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $chapters = Chapter::query()
            ->with(['academicClass', 'subject'])
            ->orderBy('name')
            ->get();

        return view('admin.index', compact('questions', 'classes', 'subjects', 'chapters', 'search'));
    }
}

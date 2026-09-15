<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('question_search', ''));
        $filters = $request->validate([
            'class_id' => ['nullable', 'integer', 'min:1'],
            'subject_id' => ['nullable', 'integer', 'min:1'],
            'chapter_id' => ['nullable', 'integer', 'min:1'],
            'creator_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $hasFilters = $search !== '' || count(array_filter($filters)) > 0;

        $questions = Question::query()
            ->with(['academicClass', 'subject', 'chapter', 'options', 'creator'])
            ->when($filters['class_id'] ?? null, fn ($query, $id) => $query->where('academic_class_id', $id))
            ->when($filters['subject_id'] ?? null, fn ($query, $id) => $query->where('subject_id', $id))
            ->when($filters['chapter_id'] ?? null, fn ($query, $id) => $query->where('chapter_id', $id))
            ->when($filters['creator_id'] ?? null, fn ($query, $id) => $query->where('created_by', $id))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question_text', 'like', '%' . $search . '%')
                        ->orWhereHas('academicClass', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('subject', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('chapter', fn ($query) => $query->where('name', 'like', '%' . $search . '%'))
                        ->orWhereHas('creator', fn ($query) => $query->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->with('academicClasses')->orderBy('name')->get();
        $chapters = Chapter::query()
            ->with(['academicClass', 'subject'])
            ->orderBy('name')
            ->get();

        $creators = Admin::query()->whereIn('id', Question::query()->select('created_by'))->orderBy('name')->get(['id', 'name']);

        return view('admin.index', compact('questions', 'classes', 'subjects', 'chapters', 'search', 'filters', 'hasFilters', 'creators'));
    }
}

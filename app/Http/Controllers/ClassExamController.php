<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassExamController extends Controller
{
    public function directory(): View
    {
        $classesQuery = AcademicClass::query()
            ->withCount('exams')
            ->when(
                auth()->check() && ! auth()->user()->is_admin,
                fn ($query) => $query->whereKey(auth()->user()->academic_class_id)
            )
            ->orderBy('name');

        $classes = $classesQuery
            ->get()
            ->filter(fn (AcademicClass $class) => $class->exams_count > 0)
            ->values();

        $subjectCounts = Exam::query()
            ->selectRaw('academic_class_id, count(distinct subject_id) as subjects_count')
            ->when(
                auth()->check() && ! auth()->user()->is_admin,
                fn ($query) => $query->where('academic_class_id', auth()->user()->academic_class_id)
            )
            ->groupBy('academic_class_id')
            ->pluck('subjects_count', 'academic_class_id');

        $classes->each(function (AcademicClass $class) use ($subjectCounts) {
            $class->subjects_count = (int) ($subjectCounts[$class->id] ?? 0);
        });

        $summary = [
            'classes' => $classes->count(),
            'exams' => $classes->sum('exams_count'),
            'subjects' => (int) $subjectCounts->sum(),
        ];

        return view('exams.directory', compact('classes', 'summary'));
    }

    public function index(Request $request, string $classSlug): View
    {
        $academicClass = AcademicClass::findBySlug($classSlug) ?? abort(404);

        if (
            auth()->check()
            && ! auth()->user()->is_admin
            && (int) auth()->user()->academic_class_id !== (int) $academicClass->id
        ) {
            abort(403);
        }

        $examRelations = ['academicClass', 'subject', 'chapter', 'questions'];

        if (auth()->check() && ! auth()->user()->is_admin) {
            $examRelations['attempts'] = fn ($query) => $query
                ->where('user_id', auth()->id())
                ->latest('submitted_at')
                ->latest();
        }

        $exams = Exam::query()
            ->with($examRelations)
            ->where('academic_class_id', $academicClass->id)
            ->latest()
            ->get();

        $exams = Exam::sortByChapter($exams);

        $status = $request->query('status');
        if (in_array($status, ['running', 'upcoming'], true)) {
            $exams = $exams->filter(fn (Exam $exam) => $exam->scheduleStatus() === $status)->values();
        }

        return view('classes.exams', [
            'academicClass' => $academicClass,
            'exams' => $exams,
        ]);
    }
}

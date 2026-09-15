<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function index(): View
    {
        $studentCount = Student::query()
            ->where('is_admin', false)
            ->count();

        return view('admin.students.index', compact('studentCount'));
    }

    public function data(Request $request): JsonResponse
    {
        $columns = [
            'students.name',
            'students.email',
            'students.phone',
            'academic_classes.name',
            'schools.title',
            'exam_attempts_count',
            'exam_attempts_max_submitted_at',
            'students.created_at',
        ];

        $baseQuery = Student::query()
            ->select('students.*')
            ->where('students.is_admin', false)
            ->leftJoin('academic_classes', 'students.academic_class_id', '=', 'academic_classes.id')
            ->leftJoin('schools', 'students.school_id', '=', 'schools.id')
            ->with(['academicClass', 'school'])
            ->withCount('examAttempts')
            ->withMax('examAttempts', 'submitted_at');

        $recordsTotal = Student::query()
            ->where('is_admin', false)
            ->count();

        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query
                    ->where('students.name', 'like', "%{$search}%")
                    ->orWhere('students.email', 'like', "%{$search}%")
                    ->orWhere('students.phone', 'like', "%{$search}%")
                    ->orWhere('academic_classes.name', 'like', "%{$search}%")
                    ->orWhere('schools.title', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->count('students.id');

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'students.name';

        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;

        $students = $baseQuery
            ->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $students->map(fn (Student $student) => [
                e($student->name),
                e($student->email),
                e($student->phone ?: '-'),
                e($student->academicClass->name ?? '-'),
                e($student->school->title ?? '-'),
                $student->exam_attempts_count,
                $student->exam_attempts_max_submitted_at
                    ? Carbon::parse($student->exam_attempts_max_submitted_at)->format('M d, Y h:i A')
                    : '-',
                optional($student->created_at)->format('M d, Y'),
                '<button class="button secondary small js-change-password" type="button" data-action="' . e(route('admin.students.password.update', $student)) . '" data-student-name="' . e($student->name) . '" data-student-contact="' . e($student->phone ?: $student->email) . '">Change Password</button>',
            ]),
        ]);
    }

    public function updatePassword(Request $request, Student $student): RedirectResponse
    {
        abort_if($student->is_admin, 404);

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.students.index')
                ->withErrors($validator, 'studentPassword')
                ->withInput([
                    'password_student_id' => $student->id,
                    'password_student_name' => $student->name,
                    'password_student_contact' => $student->phone ?: $student->email,
                    'password_action' => route('admin.students.password.update', $student),
                ]);
        }

        $data = $validator->validated();

        $student->forceFill([
            'password' => $data['password'],
        ])->save();

        return redirect()
            ->route('admin.students.index')
            ->with('status', $student->name . "'s password changed.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function index(): View
    {
        $students = User::query()
            ->where('is_admin', false)
            ->with(['academicClass', 'school'])
            ->withCount('examAttempts')
            ->withMax('examAttempts', 'submitted_at')
            ->orderBy('name')
            ->get();

        return view('admin.students.index', compact('students'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\View\View;

class AdminResultController extends Controller
{
    public function index(): View
    {
        $examAttempts = ExamAttempt::query()
            ->with(['exam.academicClass', 'exam.subject', 'exam.chapter', 'user'])
            ->latest('submitted_at')
            ->get();

        return view('admin.results.index', compact('examAttempts'));
    }
}

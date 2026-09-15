<?php

namespace App\Http\Controllers;

use App\Models\CustomExamAttempt;
use Illuminate\View\View;

class AdminCustomResultController extends Controller
{
    public function index(): View
    {
        $attempts = CustomExamAttempt::query()
            ->with([
                'user.academicClass',
                'admin',
                'customExam.academicClass',
                'customExam.subject',
                'customExam.chapter',
            ])
            ->latest('submitted_at')
            ->get();

        return view('admin.custom-results.index', compact('attempts'));
    }
}

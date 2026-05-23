<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\View\View;

class AdminResultController extends Controller
{
    public function index(): View
    {
        return $this->resultsView();
    }

    public function exam(Exam $exam): View
    {
        $exam->load(['academicClass', 'subject', 'chapter']);

        return $this->resultsView($exam);
    }

    private function resultsView(?Exam $exam = null): View
    {
        $examAttempts = ExamAttempt::query()
            ->with(['exam.academicClass', 'exam.subject', 'exam.chapter', 'user'])
            ->when($exam, fn ($query) => $query->where('exam_id', $exam->id))
            ->latest('submitted_at')
            ->get();

        $participantCount = $examAttempts->count();
        $highestAttempt = $examAttempts->sortByDesc('score')->first();
        $attemptPositions = $this->attemptPositions($examAttempts);

        return view('admin.results.index', compact(
            'examAttempts',
            'exam',
            'participantCount',
            'highestAttempt',
            'attemptPositions'
        ));
    }

    private function attemptPositions($examAttempts): array
    {
        $positions = [];
        $currentPosition = 0;
        $previousScore = null;

        foreach ($examAttempts->sortByDesc('score')->values() as $attempt) {
            if ($previousScore === null || (int) $attempt->score !== (int) $previousScore) {
                $currentPosition++;
            }

            $positions[$attempt->id] = $currentPosition;
            $previousScore = $attempt->score;
        }

        return $positions;
    }
}

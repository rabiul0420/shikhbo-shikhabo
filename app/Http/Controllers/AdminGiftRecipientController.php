<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\GiftAward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminGiftRecipientController extends Controller
{
    public function index(): View
    {
        $giftRecipients = $this->giftRecipients();

        return view('admin.gift-recipients.index', compact('giftRecipients'));
    }

    public function markGiven(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $attempt->load(['exam', 'user']);

        [$position, $giftTitle] = $this->giftForAttempt($attempt);
        abort_unless($giftTitle, 404);

        GiftAward::updateOrCreate(
            ['exam_attempt_id' => $attempt->id],
            [
                'position' => $position,
                'gift_title' => $giftTitle,
                'status' => 'given',
                'given_at' => now(),
                'given_by' => $request->user()->id,
            ]
        );

        return back()->with('status', 'Gift marked as given.');
    }

    private function giftRecipients(): Collection
    {
        $exams = Exam::query()
            ->with([
                'academicClass',
                'subject',
                'chapter',
                'attempts.user.academicClass',
                'attempts.admin.academicClass',
                'attempts.giftAward',
            ])
            ->where(function ($query) {
                $query
                    ->whereNotNull('first_prize')
                    ->orWhereNotNull('second_prize')
                    ->orWhereNotNull('third_prize');
            })
            ->latest()
            ->get();

        return $exams
            ->flatMap(function (Exam $exam) {
                $positions = $this->attemptPositions($exam->attempts);

                return $exam->attempts
                    ->filter(function (ExamAttempt $attempt) use ($exam, $positions) {
                        return (bool) $exam->prizeForPosition($positions[$attempt->id] ?? 0);
                    })
                    ->map(function (ExamAttempt $attempt) use ($exam, $positions) {
                        $position = $positions[$attempt->id];

                        return (object) [
                            'attempt' => $attempt,
                            'exam' => $exam,
                            'position' => $position,
                            'gift_title' => $exam->prizeForPosition($position),
                            'award' => $attempt->giftAward,
                        ];
                    });
            })
            ->sortBy([
                ['exam.title', 'asc'],
                ['position', 'asc'],
                ['attempt.score', 'desc'],
            ])
            ->values();
    }

    private function giftForAttempt(ExamAttempt $attempt): array
    {
        $attempts = ExamAttempt::query()
            ->where('exam_id', $attempt->exam_id)
            ->get();
        $positions = $this->attemptPositions($attempts);
        $position = $positions[$attempt->id] ?? 0;

        return [$position, $attempt->exam->prizeForPosition($position)];
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

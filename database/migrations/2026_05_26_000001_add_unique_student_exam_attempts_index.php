<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('exam_attempts')
            ->select('exam_id', 'user_id')
            ->whereNotNull('user_id')
            ->groupBy('exam_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('exam_id')
            ->each(function ($duplicate): void {
                $attemptIds = DB::table('exam_attempts')
                    ->where('exam_id', $duplicate->exam_id)
                    ->where('user_id', $duplicate->user_id)
                    ->orderByDesc('submitted_at')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->pluck('id');

                $attemptIdsToDelete = $attemptIds->skip(1)->values();

                if ($attemptIdsToDelete->isEmpty()) {
                    return;
                }

                DB::table('exam_attempt_answers')
                    ->whereIn('exam_attempt_id', $attemptIdsToDelete)
                    ->delete();

                DB::table('exam_attempts')
                    ->whereIn('id', $attemptIdsToDelete)
                    ->delete();
            });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->unique(['exam_id', 'user_id'], 'exam_attempts_exam_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropUnique('exam_attempts_exam_id_user_id_unique');
        });
    }
};

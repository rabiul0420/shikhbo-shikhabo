<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['academic_class_id', 'subject_id']);
        });

        if (Schema::hasColumn('subjects', 'academic_class_id')) {
            DB::table('subjects')
                ->whereNotNull('academic_class_id')
                ->orderBy('id')
                ->get(['id', 'academic_class_id'])
                ->each(function ($subject) {
                    DB::table('academic_class_subject')->updateOrInsert(
                        [
                            'academic_class_id' => $subject->academic_class_id,
                            'subject_id' => $subject->id,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                });
        }

        DB::table('chapters')
            ->whereNotNull('academic_class_id')
            ->whereNotNull('subject_id')
            ->distinct()
            ->get(['academic_class_id', 'subject_id'])
            ->each(function ($chapter) {
                DB::table('academic_class_subject')->updateOrInsert(
                    [
                        'academic_class_id' => $chapter->academic_class_id,
                        'subject_id' => $chapter->subject_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_class_subject');
    }
};

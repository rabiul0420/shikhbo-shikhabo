<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['academic_class_id', 'subject_id', 'name']);
        });

        $classMap = [];
        foreach (DB::table('quizzes')->whereNotNull('class_name')->where('class_name', '!=', '')->distinct()->pluck('class_name') as $name) {
            $classMap[$name] = DB::table('academic_classes')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $subjectMap = [];
        foreach (DB::table('quizzes')->whereNotNull('subject_name')->where('subject_name', '!=', '')->distinct()->pluck('subject_name') as $name) {
            $subjectMap[$name] = DB::table('subjects')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $quizzes = DB::table('quizzes')
            ->whereNotNull('class_name')
            ->where('class_name', '!=', '')
            ->whereNotNull('subject_name')
            ->where('subject_name', '!=', '')
            ->whereNotNull('chapter_name')
            ->where('chapter_name', '!=', '')
            ->get(['class_name', 'subject_name', 'chapter_name']);

        foreach ($quizzes as $quiz) {
            DB::table('chapters')->updateOrInsert(
                [
                    'academic_class_id' => $classMap[$quiz->class_name],
                    'subject_id' => $subjectMap[$quiz->subject_name],
                    'name' => $quiz->chapter_name,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_classes');
    }
};

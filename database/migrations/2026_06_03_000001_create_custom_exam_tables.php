<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_exam_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['custom_exam_id', 'question_id']);
        });

        Schema::create('custom_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('custom_exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->nullable();
            $table->unsignedSmallInteger('marks_awarded')->default(0);
            $table->timestamps();

            $table->unique(['custom_exam_attempt_id', 'question_id', 'question_option_id'], 'custom_attempt_question_option_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_exam_attempt_answers');
        Schema::dropIfExists('custom_exam_attempts');
        Schema::dropIfExists('custom_exam_question');
        Schema::dropIfExists('custom_exams');
    }
};

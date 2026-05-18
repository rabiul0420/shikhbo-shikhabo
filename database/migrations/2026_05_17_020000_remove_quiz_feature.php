<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('academic_class_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->after('academic_class_id')->constrained()->nullOnDelete();
            $table->foreignId('chapter_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
        });

        $questions = DB::table('questions')
            ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
            ->select(
                'questions.id',
                'quizzes.class_name',
                'quizzes.subject_name',
                'quizzes.chapter_name',
            )
            ->get();

        foreach ($questions as $question) {
            $academicClassId = DB::table('academic_classes')
                ->where('name', $question->class_name)
                ->value('id');
            $subjectId = DB::table('subjects')
                ->where('name', $question->subject_name)
                ->value('id');
            $chapterId = DB::table('chapters')
                ->where('name', $question->chapter_name)
                ->where('academic_class_id', $academicClassId)
                ->where('subject_id', $subjectId)
                ->value('id');

            DB::table('questions')
                ->where('id', $question->id)
                ->update([
                    'academic_class_id' => $academicClassId,
                    'subject_id' => $subjectId,
                    'chapter_id' => $chapterId,
                ]);
        }

        Schema::dropIfExists('quiz_attempt_answers');
        Schema::dropIfExists('quiz_attempts');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quiz_id');
        });

        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('pass_mark')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('class_name')->nullable();
            $table->string('chapter_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->dropConstrainedForeignId('chapter_id');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('academic_class_id');
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['in_progress', 'submitted', 'graded'])->default('in_progress');
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total_marks')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->nullable();
            $table->unsignedSmallInteger('marks_awarded')->default(0);
            $table->timestamps();
            $table->unique(['quiz_attempt_id', 'question_id', 'question_option_id'], 'attempt_question_option_unique');
        });
    }
};

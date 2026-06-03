<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomExam extends Model
{
    protected $fillable = [
        'user_id',
        'academic_class_id',
        'subject_id',
        'chapter_id',
        'title',
        'question_count',
        'total_marks',
    ];

    protected function casts(): array
    {
        return [
            'question_count' => 'integer',
            'total_marks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'custom_exam_question')->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CustomExamAttempt::class);
    }
}

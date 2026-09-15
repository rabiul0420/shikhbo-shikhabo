<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomExamAttempt extends Model
{
    protected $fillable = [
        'custom_exam_id',
        'user_id',
        'admin_id',
        'status',
        'score',
        'total_marks',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function customExam(): BelongsTo
    {
        return $this->belongsTo(CustomExam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CustomExamAttemptAnswer::class);
    }
}

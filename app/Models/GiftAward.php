<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftAward extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'position',
        'gift_title',
        'status',
        'given_at',
        'given_by',
    ];

    protected function casts(): array
    {
        return [
            'given_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function giver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'given_by');
    }
}

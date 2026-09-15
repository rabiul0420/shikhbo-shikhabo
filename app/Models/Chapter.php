<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use \App\Models\Concerns\HasAcademicOwner;

    protected $fillable = [
        'created_by',
        'academic_class_id',
        'subject_id',
        'chapter_no',
        'name',
    ];

    protected $casts = [
        'academic_class_id' => 'integer',
        'subject_id' => 'integer',
    ];

    public function getDisplayNameAttribute(): string
    {
        return filled($this->chapter_no)
            ? $this->chapter_no . ' - ' . $this->name
            : $this->name;
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}

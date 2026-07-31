<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Exam extends Model
{
    protected $fillable = [
        'created_by',
        'academic_class_id',
        'subject_id',
        'chapter_id',
        'title',
        'slug',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'first_prize',
        'second_prize',
        'third_prize',
    ];

    protected static function booted(): void
    {
        static::saving(function (Exam $exam) {
            if ($exam->isDirty('title') || blank($exam->slug)) {
                $exam->slug = static::uniqueSlugFor($exam->title, $exam->id);
            }
        });
    }

    public static function uniqueSlugFor(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'exam';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function findBySlug(string $slug): ?self
    {
        if (is_numeric($slug)) {
            return static::query()->whereKey($slug)->first();
        }

        return static::query()->where('slug', $slug)->first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function scheduleStatus(): string
    {
        $today = today();

        if ($this->starts_at && $this->starts_at->gt($today)) {
            return 'upcoming';
        }

        if ($this->ends_at && $this->ends_at->lt($today)) {
            return 'expired';
        }

        return 'running';
    }

    public function isRunning(): bool
    {
        return $this->scheduleStatus() === 'running';
    }

    public function prizeForPosition(int $position): ?string
    {
        return match ($position) {
            1 => $this->first_prize,
            2 => $this->second_prize,
            3 => $this->third_prize,
            default => null,
        };
    }

    public function hasPrizes(): bool
    {
        return (bool) ($this->first_prize || $this->second_prize || $this->third_prize);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->belongsToMany(Question::class)->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcademicClass extends Model
{
    use \App\Models\Concerns\HasAcademicOwner;

    protected $fillable = ['name', 'created_by', 'priority'];

    protected $casts = ['priority' => 'integer'];

    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->orderBy('priority')->orderBy('name')->orderBy('id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }


    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function getSlugAttribute(): string
    {
        $slug = Str::slug($this->name);

        return $slug !== '' ? $slug : 'class-'.$this->id;
    }

    public static function findBySlug(string $slug): ?self
    {
        if (is_numeric($slug)) {
            return static::query()->whereKey($slug)->first();
        }

        return static::query()
            ->get()
            ->first(fn (self $class) => $class->slug === $slug);

    }
}

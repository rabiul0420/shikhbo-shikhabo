<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcademicClass extends Model
{
    protected $fillable = ['name'];

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

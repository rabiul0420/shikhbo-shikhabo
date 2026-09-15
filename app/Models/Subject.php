<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use \App\Models\Concerns\HasAcademicOwner;

    protected $fillable = ['name', 'created_by'];

    public function academicClasses(): BelongsToMany
    {
        return $this->belongsToMany(AcademicClass::class)->withTimestamps();
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\AdminFactory;

class Admin extends User
{
    protected $table = 'admins';
    protected $attributes = ['is_admin' => true];

    public function examAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'admin_id');
    }

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}

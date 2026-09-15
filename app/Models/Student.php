<?php

namespace App\Models;

use Database\Factories\StudentFactory;

class Student extends User
{
    protected $table = 'students';
    protected $attributes = ['is_admin' => false, 'is_super_admin' => false];

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }
}

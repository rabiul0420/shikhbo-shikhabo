<?php

namespace Database\Factories;

use App\Models\Student;

class StudentFactory extends UserFactory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return parent::definition() + ['is_admin' => false];
    }
}

<?php

namespace Database\Factories;

use App\Models\Admin;

class AdminFactory extends UserFactory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return parent::definition() + ['is_admin' => true];
    }
}

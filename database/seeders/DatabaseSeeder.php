<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        School::query()
            ->where('title', 'Shikhbo Shikhabo School')
            ->update(['title' => 'Bd ModelTest School']);

        School::query()
            ->whereIn('title', ['Shikhbo Shikhabo', 'Bd ModelTest'])
            ->whereNull('address')
            ->delete();

        foreach ([
            [
                'title' => 'Bd ModelTest School',
                'address' => 'Mirpur, Dhaka',
                'status' => 'active',
            ],
            [
                'title' => 'Green Valley School',
                'address' => 'Dhanmondi, Dhaka',
                'status' => 'active',
            ],
            [
                'title' => 'Sunrise Model School',
                'address' => 'Uttara, Dhaka',
                'status' => 'active',
            ],
            [
                'title' => 'Knowledge Academy',
                'address' => 'Chattogram',
                'status' => 'pending',
            ],
            [
                'title' => 'Future Scholars School',
                'address' => 'Sylhet',
                'status' => 'pending',
            ],
        ] as $school) {
            School::updateOrCreate(
                ['title' => $school['title']],
                $school,
            );
        }

        $defaultSchoolId = School::query()
            ->where('title', 'Bd ModelTest School')
            ->value('id');

        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '01000000000',
                'school_id' => $defaultSchoolId,
                'password' => 'password',
                'is_admin' => true,
                'is_super_admin' => true,
            ],
        );

        Student::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'phone' => '01900000000',
                'school_id' => $defaultSchoolId,
                'password' => 'password',
                'is_admin' => false,
                'is_super_admin' => false,
            ],
        );
    }
}

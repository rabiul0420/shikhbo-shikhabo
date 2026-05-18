<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_access_question_add_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/questions/create')
            ->assertOk()
            ->assertSee('Question Add')
            ->assertSee('Select class')
            ->assertSee('Select subject')
            ->assertSee('Select oddhay / chapter');
    }

    public function test_admin_can_manage_academic_structure(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/admin/classes', ['name' => 'Class 10'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post('/admin/subjects', ['name' => 'Physics'])
            ->assertRedirect();

        $classId = \App\Models\AcademicClass::where('name', 'Class 10')->value('id');
        $subjectId = \App\Models\Subject::where('name', 'Physics')->value('id');

        $this->actingAs($admin)
            ->post('/admin/chapters', [
                'academic_class_id' => $classId,
                'subject_id' => $subjectId,
                'name' => 'Chapter 1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('academic_classes', ['name' => 'Class 10']);
        $this->assertDatabaseHas('subjects', ['name' => 'Physics']);
        $this->assertDatabaseHas('chapters', [
            'academic_class_id' => $classId,
            'subject_id' => $subjectId,
            'name' => 'Chapter 1',
        ]);
    }

}

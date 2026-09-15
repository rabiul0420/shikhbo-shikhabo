<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AccountSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_identical_ids_and_contacts_have_independent_login_sessions_and_logout(): void
    {
        $admin = Admin::factory()->create(['id' => 50, 'email' => 'same@example.test', 'phone' => '01700000000', 'password' => 'admin-password']);
        $student = Student::factory()->create(['id' => 50, 'email' => 'same@example.test', 'phone' => '01700000000', 'password' => 'student-password']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'student-password'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'admin-password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
        $this->get('/my-profile')->assertRedirect();

        $this->post('/login', ['phone' => $student->phone, 'password' => 'admin-password'])->assertSessionHasErrors('phone');
        $this->post('/login', ['phone' => $student->phone, 'password' => 'student-password'])->assertRedirect();
        // Rehydrate both identities from their session keys, rather than cached guard users.
        Auth::forgetGuards();
        $this->get('/admin/profile')->assertOk()->assertViewHas('user', fn ($user) => $user instanceof Admin && $user->id === 50);
        $this->get('/my-profile')->assertOk();
        $this->assertAuthenticatedAs($student, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($student, 'web');
        $this->get('/admin/profile')->assertForbidden();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest('web');
    }

    public function test_password_validation_and_updates_use_admin_table_even_with_same_student_id(): void
    {
        $admin = Admin::factory()->create(['id' => 10, 'password' => 'admin-password']);
        $student = Student::factory()->create(['id' => 10, 'password' => 'student-password']);
        $hash = $student->password;
        $this->actingAs($student, 'web')->actingAs($admin, 'admin');
        $this->patch('/admin/profile/password', ['current_password' => 'student-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasErrors('current_password');
        $this->patch('/admin/profile/password', ['current_password' => 'admin-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHasNoErrors()->assertRedirect('/admin/profile');
        $this->assertSame($hash, $student->fresh()->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $admin->fresh()->password));
    }

    public function test_historical_admin_attempt_cannot_be_accessed_by_same_id_student(): void
    {
        $admin = Admin::factory()->create(['id' => 10]);
        $student = Student::factory()->create(['id' => 10]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Math']);
        $chapter = \App\Models\Chapter::create(['name' => 'Numbers', 'academic_class_id' => $class->id, 'subject_id' => $subject->id]);
        $exam = Exam::create(['title' => 'Historical admin exam', 'created_by' => $admin->id, 'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id]);
        $attempt = ExamAttempt::create(['exam_id' => $exam->id, 'admin_id' => $admin->id, 'score' => 4, 'total_marks' => 5]);
        $this->actingAs($student)->get(route('exam-attempts.result', $attempt))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.results.show', $attempt))->assertOk();
        $this->get('/admin/results')->assertOk()->assertSee($admin->name);
        $this->assertNull($attempt->user);
        $this->assertSame($admin->id, $attempt->admin->id);
    }
}

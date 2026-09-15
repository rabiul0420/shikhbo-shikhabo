<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_manage_blogs_but_cannot_access_other_modules(): void
    {
        $editor = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'content_editor']);
        $this->actingAs($editor)->get('/admin')->assertRedirect('/admin/blogs');
        $this->get('/admin/blogs')->assertOk()->assertDontSee('Student List')->assertDontSee('Question Add');
        foreach (['/admin/students', '/admin/results', '/admin/exams', '/admin/users', '/admin/academic/classes'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/questions', [])->assertForbidden();
        $this->post('/exams', [])->assertForbidden();
    }

    public function test_exam_manager_has_academic_access_but_cannot_manage_users_or_blogs(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $this->actingAs($manager)->get('/admin')->assertOk()->assertDontSee('Student List')->assertDontSee('Blog List');
        $this->get('/admin/academic/classes')->assertOk();
        $this->get('/admin/results')->assertOk();
        $this->get('/admin/blogs')->assertForbidden();
        $this->post('/admin/users', [])->assertForbidden();
        $this->post('/admin/blogs', [])->assertForbidden();
    }

    public function test_only_super_admin_can_change_roles_and_cannot_demote_super_admin(): void
    {
        $super = Admin::factory()->create(['is_admin' => true, 'is_super_admin' => true]);
        $admin = Admin::factory()->create(['is_admin' => true]);
        $url = route('admin.users.role.update', $admin);
        $this->actingAs($admin)->patch($url, ['admin_role' => 'content_editor'])->assertForbidden();
        $this->actingAs($super)->patch($url, ['admin_role' => 'content_editor'])->assertRedirect(route('admin.users.index'));
        $this->assertSame('content_editor', $admin->fresh()->adminRole());
        $this->patch($url, ['admin_role' => 'super_admin'])->assertSessionHasErrors('admin_role');
        $this->patch(route('admin.users.role.update', $super), ['admin_role' => 'admin'])->assertForbidden();
        $this->assertTrue($super->fresh()->is_super_admin);
        $this->actingAs($admin->fresh())->get('/admin/students')->assertForbidden();
    }

    public function test_super_admin_can_create_a_user_with_a_limited_role(): void
    {
        $super = Admin::factory()->create(['is_admin' => true, 'is_super_admin' => true]);
        $this->actingAs($super)->post('/admin/users', [
            'name' => 'Editor', 'email' => 'editor@example.test', 'phone' => '01712345678',
            'password' => 'password', 'password_confirmation' => 'password', 'admin_role' => 'content_editor',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('admins', ['email' => 'editor@example.test', 'admin_role' => 'content_editor', 'is_admin' => true, 'is_super_admin' => false]);
    }

    public function test_legacy_admin_keeps_access_and_student_cannot_gain_access_from_role_column(): void
    {
        $admin = Admin::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/students')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $student = Student::factory()->create(['is_admin' => false, 'admin_role' => 'admin']);
        $this->actingAs($student)->get('/admin')->assertForbidden();
    }
}

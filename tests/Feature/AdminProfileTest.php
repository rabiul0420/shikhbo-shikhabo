<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_admin_roles_can_view_and_update_only_their_own_profile(): void
    {
        $other = Student::factory()->create();
        foreach (['admin', 'exam_manager', 'content_editor', 'super_admin'] as $role) {
            $user = Admin::factory()->create([
                'is_admin' => true, 'is_super_admin' => $role === 'super_admin',
                'admin_role' => $role === 'super_admin' ? 'admin' : $role,
            ]);
            $password = $user->password;
            $this->actingAs($user)->get('/admin/profile')->assertOk()->assertSee($user->email)->assertSee('My Profile');
            $this->patch('/admin/profile', [
                'name' => 'Updated name', 'email' => $role.'@example.test', 'phone' => null,
                'id' => $other->id, 'is_admin' => false, 'is_super_admin' => true,
                'admin_role' => 'super_admin', 'password' => 'unexpected-password',
            ])->assertRedirect('/admin/profile')->assertSessionHas('status', 'Profile updated.');
            $user->refresh();
            $this->assertSame('Updated name', $user->name);
            $this->assertSame($role.'@example.test', $user->email);
            $this->assertNull($user->email_verified_at);
            $this->assertSame($role, $user->adminRole());
            $this->assertTrue($user->is_admin);
            $this->assertSame($password, $user->password);
        }
        $this->assertSame($other->name, $other->fresh()->name);
    }

    public function test_validation_rejects_duplicate_contact_details_and_accepts_unchanged_email(): void
    {
        $other = Admin::factory()->create(['phone' => '01712345678']);
        $user = Admin::factory()->create(['is_admin' => true]);
        $this->actingAs($user)->patch('/admin/profile', [
            'name' => '', 'email' => $other->email, 'phone' => $other->phone,
        ])->assertSessionHasErrors(['name', 'email', 'phone']);
        $this->assertSame($user->email, $user->fresh()->email);
        $this->patch('/admin/profile', ['name' => $user->name, 'email' => $user->email])
            ->assertSessionHasNoErrors()->assertRedirect('/admin/profile');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_guests_and_students_cannot_access_admin_profile(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
        $this->patch('/admin/profile', [])->assertRedirect('/admin/login');
        $this->actingAs(Student::factory()->create(['is_admin' => false]))
            ->get('/admin/profile')->assertForbidden();
        $this->patch('/admin/profile', [])->assertForbidden();
    }
}

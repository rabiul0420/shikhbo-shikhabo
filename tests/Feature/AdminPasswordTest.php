<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_admin_role_can_change_only_their_own_password(): void
    {
        $other = Student::factory()->create();
        $otherHash = $other->password;
        foreach (['admin', 'exam_manager', 'content_editor', 'super_admin'] as $role) {
            $user = Admin::factory()->create([
                'is_admin' => true, 'admin_role' => $role === 'super_admin' ? 'admin' : $role,
                'is_super_admin' => $role === 'super_admin', 'password' => 'old-password',
            ]);
            $token = $user->remember_token;
            $this->actingAs($user)->patch('/admin/profile/password', [
                'current_password' => 'old-password', 'password' => 'new-password',
                'password_confirmation' => 'new-password', 'id' => $other->id,
                'admin_role' => 'super_admin',
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/profile');
            $user->refresh();
            $this->assertTrue(Hash::check('new-password', $user->password));
            $this->assertFalse(Hash::check('old-password', $user->password));
            $this->assertNotSame($token, $user->remember_token);
            $this->assertSame($role, $user->adminRole());
            $this->assertAuthenticatedAs($user);
        }
        $this->assertSame($otherHash, $other->fresh()->password);
    }

    public function test_invalid_password_changes_do_not_change_the_password_or_flash_secrets(): void
    {
        $user = Admin::factory()->create(['is_admin' => true, 'password' => 'old-password']);
        $original = $user->password;
        foreach ([
            ['wrong-password', 'new-password', 'new-password', 'current_password'],
            ['old-password', 'new-password', 'mismatch', 'password'],
            ['old-password', 'short', 'short', 'password'],
            ['old-password', 'old-password', 'old-password', 'password'],
        ] as [$current, $new, $confirmation, $error]) {
            $this->actingAs($user)->from('/admin/profile')->patch('/admin/profile/password', [
                'current_password' => $current, 'password' => $new, 'password_confirmation' => $confirmation,
            ])->assertSessionHasErrors($error)
                ->assertSessionMissing('_old_input.current_password')
                ->assertSessionMissing('_old_input.password')
                ->assertSessionMissing('_old_input.password_confirmation');
            $this->assertSame($original, $user->fresh()->password);
        }
    }

    public function test_guests_and_students_cannot_change_password_through_admin_route(): void
    {
        $this->patch('/admin/profile/password', [])->assertRedirect('/admin/login');
        $this->actingAs(Student::factory()->create(['is_admin' => false]))
            ->patch('/admin/profile/password', [])->assertForbidden();
    }
}

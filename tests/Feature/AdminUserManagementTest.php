<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_creation_form_includes_password_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
        $response->assertSee('Change Password');
        $response->assertDontSee('Profile & Password');
        $response->assertSee(route('logout'), false);
        $response->assertSee('Logout');
        $response->assertSee('name="current_password"', false);
        $response->assertSee('id="new_password"', false);
        $response->assertSee('id="new_password_confirmation"', false);
    }

    public function test_admin_can_change_another_users_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.change-password', $user), [
            'current_password' => 'password',
            'password' => 'Secure-New-Password!42',
            'password_confirmation' => 'Secure-New-Password!42',
            'change_password_user_id' => $user->id,
            'change_password_user_name' => $user->name,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('Secure-New-Password!42', $user->refresh()->password));
    }

    public function test_admin_can_change_their_own_password_from_user_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.change-password', $admin), [
            'current_password' => 'password',
            'password' => 'Secure-Own-Password!42',
            'password_confirmation' => 'Secure-Own-Password!42',
            'change_password_user_id' => $admin->id,
            'change_password_user_name' => $admin->name,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('Secure-Own-Password!42', $admin->refresh()->password));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_current_admin_password_is_required_before_changing_a_user_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['is_active' => true]);
        $originalPassword = $user->password;

        $response = $this->actingAs($admin)->from(route('admin.users.index'))->post(
            route('admin.users.change-password', $user),
            [
                'current_password' => 'incorrect-password',
                'password' => 'Secure-New-Password!42',
                'password_confirmation' => 'Secure-New-Password!42',
                'change_password_user_id' => $user->id,
                'change_password_user_name' => $user->name,
            ]
        );

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasErrors(['current_password'], null, 'changePassword');
        $this->assertSame($originalPassword, $user->refresh()->password);
    }

    public function test_admin_can_send_bulk_password_reset_links(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $users = User::factory()->count(2)->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.bulk-reset-passwords'), [
            'user_ids' => $users->pluck('id')->all(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($users as $user) {
            Notification::assertSentTo($user, ResetPassword::class);
        }
    }
}

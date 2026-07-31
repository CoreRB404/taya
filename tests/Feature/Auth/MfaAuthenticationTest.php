<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\MfaCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MfaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_mfa_challenges_and_verifies_a_user(): void
    {
        config(['security.mfa.required' => true]);
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge'));

        Notification::assertSentTo($user, MfaCodeNotification::class);
        $this->get('/dashboard')->assertRedirect(route('mfa.challenge'));

        $user->forceFill([
            'mfa_code_hash' => Hash::make('123456'),
            'mfa_expires_at' => now()->addMinutes(5),
        ])->save();

        $this->post(route('mfa.verify'), ['code' => '123456'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->get('/dashboard')->assertOk();
    }
}

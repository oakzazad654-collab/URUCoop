<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_reset_link_and_set_a_new_password_with_its_token()
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });
        $this->assertNotEmpty($token);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('ตั้งรหัสผ่านใหม่');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Recovered-password-2026',
            'password_confirmation' => 'Recovered-password-2026',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('Recovered-password-2026', $user->getAuthPassword()));
    }

    public function test_unknown_email_receives_the_same_reset_request_response_without_a_notification()
    {
        Notification::fake();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'unknown@example.test'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }
}

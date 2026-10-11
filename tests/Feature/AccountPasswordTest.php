<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_password_after_confirming_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current-password-1'),
        ]);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'Current-password-1',
            'password' => 'New-password-2026',
            'password_confirmation' => 'New-password-2026',
        ])->assertRedirect('/account/settings');

        $user->refresh();
        $this->assertTrue(Hash::check('New-password-2026', $user->getAuthPassword()));
    }

    public function test_password_change_rejects_wrong_current_password_and_short_new_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current-password-1'),
        ]);

        $this->actingAs($user)->from('/account/settings')->put('/account/password', [
            'current_password' => 'incorrect-current',
            'password' => 'New-password-2026',
            'password_confirmation' => 'New-password-2026',
        ])->assertRedirect('/account/settings')->assertSessionHasErrors('current_password');

        $this->from('/account/settings')->put('/account/password', [
            'current_password' => 'Current-password-1',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertRedirect('/account/settings')->assertSessionHasErrors('password');
    }
}

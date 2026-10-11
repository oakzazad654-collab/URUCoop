<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_demo_account()
    {
        $this->seed(UserSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@uru.ac.th',
            'password' => env('URU_COOP_DEMO_PASSWORD', 'password'),
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'admin@uru.ac.th')->first());
    }
}

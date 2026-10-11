<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Faculty;
use App\Models\Staff;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_login_through_the_single_login_page()
    {
        $this->seed(UserSeeder::class);
        $response = $this->withSession(['url.intended' => '/admin/users/create'])
            ->post('/login', $this->credentials('student@uru.ac.th'));

        $response->assertRedirect('/student/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'student@uru.ac.th')->first());
    }

    public function test_advisor_is_redirected_to_the_shared_staff_dashboard()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/login', $this->credentials('advisor@uru.ac.th'));

        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'advisor@uru.ac.th')->first());
    }

    public function test_officer_is_redirected_to_the_shared_staff_dashboard()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/login', $this->credentials('officer@uru.ac.th'));

        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'officer@uru.ac.th')->first());
    }

    public function test_admin_can_login_through_the_single_login_page()
    {
        $this->seed(UserSeeder::class);
        $response = $this->post('/login', $this->credentials('admin@uru.ac.th'));

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'admin@uru.ac.th')->first());
    }

    public function test_supervisor_and_executive_can_login_and_open_their_dashboards()
    {
        $faculty = Faculty::create(['name' => 'คณะทดสอบ']);
        $supervisor = User::create([
            'name' => 'อาจารย์นิเทศทดสอบ',
            'email' => 'supervisor-login@example.test',
            'password' => bcrypt('supervisor-secret'),
            'role' => 'supervisor',
            'faculty_id' => $faculty->id,
        ]);
        Staff::create([
            'user_id' => $supervisor->id,
            'staff_code' => 'SUP-LOGIN-1',
            'faculty_id' => $faculty->id,
        ]);
        $executive = User::create([
            'name' => 'ผู้บริหารทดสอบ',
            'email' => 'executive-login@example.test',
            'password' => bcrypt('executive-secret'),
            'role' => 'executive',
        ]);

        foreach ([
            [$supervisor, 'supervisor-secret', '/supervisor/dashboard', 'ภาพรวมอาจารย์นิเทศ'],
            [$executive, 'executive-secret', '/executive/dashboard', 'ภาพรวมผู้บริหาร'],
        ] as [$user, $password, $dashboard, $heading]) {
            $this->post('/login', ['email' => $user->email, 'password' => $password])
                ->assertRedirect($dashboard);
            $this->assertAuthenticatedAs($user);
            $this->get($dashboard)->assertOk()->assertSee($heading);
            $this->get('/workflow')->assertOk();
            $this->get('/admin/dashboard')->assertForbidden();
            $this->get('/admin/users')->assertForbidden();
            $this->post('/logout')->assertRedirect('/login');
        }
    }

    public function test_home_shows_public_landing_to_guests()
    {
        $this->get('/')->assertOk()->assertSee('URU Coop-IS')->assertSee('ข่าวสารล่าสุด');
    }

    public function test_every_role_can_view_the_public_homepage()
    {
        foreach (['student', 'advisor', 'officer', 'admin', 'supervisor', 'executive'] as $role) {
            $user = User::create([
                'name' => 'ผู้ใช้ '.$role,
                'email' => $role.'-homepage@example.test',
                'password' => bcrypt('secret'),
                'role' => $role,
            ]);

            $this->actingAs($user)->get('/')
                ->assertOk()
                ->assertSee('URU Coop-IS')
                ->assertSee('ข่าวสารล่าสุด');
        }
    }

    public function test_student_staff_and_admin_dashboards_link_to_the_public_homepage()
    {
        $this->seed(UserSeeder::class);
        $dashboards = [
            ['student@uru.ac.th', '/student/dashboard'],
            ['advisor@uru.ac.th', '/staff/dashboard'],
            ['officer@uru.ac.th', '/staff/dashboard'],
            ['admin@uru.ac.th', '/admin/dashboard'],
        ];

        foreach ($dashboards as [$email, $path]) {
            $user = User::where('email', $email)->firstOrFail();

            $this->actingAs($user)->get($path)
                ->assertOk()
                ->assertSee('หน้าแรก URU Coop-IS')
                ->assertSee('href="'.route('home').'"', false);
        }
    }

    public function test_authenticated_users_are_redirected_from_login_by_role()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();

        $this->actingAs($student)->get('/login')->assertRedirect('/student/dashboard');
    }

    private function credentials($email)
    {
        return [
            'email' => $email,
            'password' => env('URU_COOP_DEMO_PASSWORD', 'password'),
        ];
    }
}

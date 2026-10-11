<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\Faculty;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->actingAs(User::where('email', 'admin@uru.ac.th')->first());
    }

    public function test_admin_can_view_paginated_user_management_page()
    {
        $this->get('/admin/users')->assertOk()->assertSee('บัญชีในระบบ')->assertSee('ใช้งานอยู่');
    }

    public function test_admin_can_reset_password_and_soft_delete_a_user()
    {
        $user = User::where('email', 'student@uru.ac.th')->first();

        $this->post('/admin/users/'.$user->id.'/reset-password')->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertTrue(Hash::check(env('URU_COOP_DEMO_PASSWORD', 'password'), $user->password));

        $this->delete('/admin/users/'.$user->id)->assertRedirect('/admin/users');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_admin_can_create_supervisor_and_executive_accounts()
    {
        $faculty = Faculty::firstOrFail();

        $this->post('/admin/users', [
            'name' => 'อาจารย์นิเทศใหม่',
            'email' => 'new-supervisor@example.test',
            'role' => 'supervisor',
            'password' => 'supervisor-secret',
            'faculty_id' => $faculty->id,
        ])->assertRedirect('/admin/users');

        $supervisor = User::where('email', 'new-supervisor@example.test')->firstOrFail();
        $this->assertDatabaseHas('staff', [
            'user_id' => $supervisor->id,
            'staff_code' => 'SUP-'.$supervisor->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->post('/admin/users', [
            'name' => 'ผู้บริหารใหม่',
            'email' => 'new-executive@example.test',
            'role' => 'executive',
            'password' => 'executive-secret',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'new-executive@example.test',
            'role' => 'executive',
        ]);
    }

    public function test_admin_can_create_a_company_account_linked_to_one_company()
    {
        $company = Company::create([
            'name' => 'บริษัททดสอบสำหรับบัญชี',
            'company_code' => 'TEST-PORTAL-ACCOUNT',
            'status' => 'active',
        ]);

        $this->get('/admin/users/create')
            ->assertOk()
            ->assertSee('สถานประกอบการ');

        $this->post('/admin/users', [
            'name' => 'ผู้ประสานงานบริษัท',
            'email' => 'company-contact@example.test',
            'role' => 'company',
            'password' => 'company-secret',
            'company_id' => $company->id,
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'company-contact@example.test',
            'role' => 'company',
            'company_id' => $company->id,
        ]);
    }

    public function test_admin_created_mentor_is_linked_to_company_contact()
    {
        $company = Company::create([
            'name' => 'บริษัทสำหรับผู้นิเทศ',
            'company_code' => 'TEST-MENTOR-ACCOUNT',
            'status' => 'active',
        ]);

        $this->post('/admin/users', [
            'name' => 'ผู้นิเทศฝ่ายผลิต',
            'email' => 'admin-created-mentor@example.test',
            'role' => 'mentor',
            'password' => 'mentor-secret',
            'company_id' => $company->id,
            'position_name' => 'หัวหน้างาน',
            'phone' => '0812345678',
        ])->assertRedirect('/admin/users');

        $mentor = User::where('email', 'admin-created-mentor@example.test')->firstOrFail();
        $this->assertDatabaseHas('company_contacts', [
            'user_id' => $mentor->id,
            'company_id' => $company->id,
            'contact_type' => 'MENTOR',
            'status' => 'ACTIVE',
            'position_name' => 'หัวหน้างาน',
        ]);

        $this->actingAs($mentor)->get(route('mentor.dashboard'))->assertOk();
    }
}

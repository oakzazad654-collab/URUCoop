<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_only_reports_within_their_role()
    {
        $student = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'reports-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);

        $this->actingAs($student)
            ->get('/reports')
            ->assertOk()
            ->assertSee('สถานะใบสมัคร CWIE 01')
            ->assertSee('เอกสารที่ต้องตรวจสอบ')
            ->assertDontSee('กำหนดการนิเทศ');

        $this->actingAs($student)
            ->get('/reports/supervision-schedule')
            ->assertForbidden();
    }

    public function test_administrator_can_view_and_export_reports_from_the_web()
    {
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'reports-admin@example.test',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/reports')
            ->assertOk()
            ->assertSee('จำนวนนักศึกษาตามสถานประกอบการ')
            ->assertSee('กำหนดการนิเทศ');

        $this->actingAs($admin)
            ->get('/reports/application-status')
            ->assertOk()
            ->assertSee('ยังไม่มีข้อมูลสำหรับรายงานนี้')
            ->assertSee('ดาวน์โหลด CSV')
            ->assertSee('ดาวน์โหลด PDF');

        $this->actingAs($admin)
            ->get('/reports/application-status?format=csv')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $this->actingAs($admin)
            ->get('/reports/application-status?format=pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}

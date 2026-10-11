<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CooperativeEducationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_normalized_application_counts()
    {
        $studentUser = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'dashboard-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $faculty = Faculty::create(['name' => 'คณะแดชบอร์ด']);
        $program = Program::create([
            'program_code' => 'DASH-CS',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create(['name' => 'บริษัทแดชบอร์ด']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'DASH0001',
            'program_id' => $program->program_id,
        ]);
        CoopApplication::create([
            'application_no' => 'APP-DASH-0001',
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_date' => '2026-04-01',
            'application_status' => 'SUBMITTED',
        ]);

        $this->actingAs($studentUser)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats[0]['value'] === 1 && $stats[1]['value'] === 0;
            });
    }
}
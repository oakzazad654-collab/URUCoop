<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkflowWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_workflow_and_create_application_from_web()
    {
        $faculty = Faculty::create(['name' => 'คณะทดสอบ']);
        $studentUser = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'workflow-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $advisor = User::create([
            'name' => 'อาจารย์ทดสอบ',
            'email' => 'workflow-advisor@example.test',
            'password' => Hash::make('secret'),
            'role' => 'advisor',
            'faculty_id' => $faculty->id,
        ]);
        $studentUser->advisor_id = $advisor->id;
        $studentUser->save();
        $program = Program::create([
            'program_code' => 'WF-CS',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'WF0001',
            'program_id' => $program->program_id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create([
            'name' => 'บริษัททดสอบ',
            'company_name_th' => 'บริษัททดสอบ',
            'company_code' => 'WF-COMPANY',
            'status' => 'active',
        ]);

        $this->actingAs($studentUser)
            ->get('/workflow')
            ->assertOk()
            ->assertSee('Workflow สหกิจศึกษา')
            ->assertSee('ใบสมัครและตรวจคุณสมบัติ');

        $this->actingAs($studentUser)->post('/workflow/applications', [
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'desired_position' => 'นักพัฒนาซอฟต์แวร์ฝึกงาน',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'student_note' => 'สมัครผ่านหน้าเว็บ',
        ])->assertRedirect('/workflow');

        $this->assertDatabaseHas('coop_applications', [
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'application_status' => 'DRAFT',
        ]);

        $application = \App\Models\CoopApplication::where('student_id', $student->student_id)->firstOrFail();
        $this->actingAs($studentUser)
            ->post('/workflow/applications/'.$application->application_id.'/submit')
            ->assertRedirect('/workflow');
        $this->assertDatabaseHas('coop_applications', [
            'application_id' => $application->application_id,
            'application_status' => 'SUBMITTED',
        ]);

        $this->actingAs($advisor)
            ->get('/workflow')
            ->assertOk()
            ->assertSee('ตรวจคุณสมบัติใบสมัคร');
        $this->actingAs($advisor)->post('/workflow/applications/'.$application->application_id.'/review', [
            'academic_result' => '1',
            'behavior_result' => '1',
            'activity_result' => '1',
            'health_result' => '1',
            'result' => 'QUALIFIED',
            'comment' => 'ผ่านเกณฑ์',
        ])->assertRedirect('/workflow');
        $this->assertDatabaseHas('coop_applications', [
            'application_id' => $application->application_id,
            'application_status' => 'APPROVED',
            'qualification_result' => 'QUALIFIED',
        ]);
    }
}
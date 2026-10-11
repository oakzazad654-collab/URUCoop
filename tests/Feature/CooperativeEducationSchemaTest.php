<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Placement;
use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkPlan;
use Database\Seeders\CooperativeEducationSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CooperativeEducationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalized_student_application_placement_and_work_plan_are_related()
    {
        $role = Role::create(['role_code' => 'STUDENT', 'role_name' => 'นักศึกษา']);
        $user = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'student-schema@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
            'role_id' => $role->role_id,
        ]);
        $faculty = Faculty::create(['name' => 'คณะทดสอบ']);
        $program = Program::create([
            'program_code' => 'TEST-CS',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $student = Student::create([
            'user_id' => $user->id,
            'student_code' => 'TEST0001',
            'program_id' => $program->program_id,
        ]);
        $company = Company::create([
            'name' => 'บริษัททดสอบ',
            'company_code' => 'TEST-COMPANY',
            'company_name_th' => 'บริษัททดสอบ',
        ]);
        $application = CoopApplication::create([
            'application_no' => 'APP-TEST-0001',
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_date' => '2026-04-01',
            'application_status' => 'APPROVED',
            'qualification_result' => 'QUALIFIED',
        ]);
        $placement = Placement::create([
            'placement_no' => 'PLC-TEST-0001',
            'application_id' => $application->application_id,
            'student_id' => $student->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'job_position' => 'นักพัฒนาระบบฝึกหัด',
            'job_description' => 'พัฒนาระบบทดสอบ',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => 'ACCEPTED',
        ]);
        $workPlan = WorkPlan::create([
            'placement_id' => $placement->placement_id,
            'plan_title' => 'แผนทดสอบ',
        ]);

        $this->assertSame($role->role_id, $user->fresh()->assignedRole->role_id);
        $this->assertSame($user->id, $student->fresh()->user->id);
        $this->assertSame($company->id, $application->fresh()->company->id);
        $this->assertSame($student->student_id, $placement->fresh()->student->student_id);
        $this->assertSame($workPlan->work_plan_id, $placement->fresh()->workPlan->work_plan_id);
    }

    public function test_cooperative_seeder_maps_legacy_users_and_companies()
    {
        $faculty = Faculty::create(['name' => 'คณะตัวอย่าง']);
        $user = User::create([
            'name' => 'นักศึกษาจากระบบเดิม',
            'email' => 'legacy-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
            'student_number' => 'LEGACY0001',
            'faculty_id' => $faculty->id,
        ]);
        $company = Company::create(['name' => 'บริษัทเดิม']);

        $this->seed(CooperativeEducationSeeder::class);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'LEGACY0001',
        ]);
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'student_code' => 'LEGACY0001',
        ]);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'company_name_th' => 'บริษัทเดิม',
        ]);
        $this->assertDatabaseHas('roles', ['role_code' => 'STUDENT']);
    }

    public function test_full_database_seeder_creates_normalized_demo_accounts()
    {
        $this->seed(DatabaseSeeder::class);

        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $this->assertSame('STUDENT', $student->assignedRole->role_code);
        $this->assertDatabaseHas('students', [
            'user_id' => $student->id,
            'student_code' => 'DEMO0001',
        ]);
        $this->assertDatabaseHas('companies', [
            'company_name_th' => 'บริษัท เทคโนโลยีไทย จำกัด',
        ]);
        $this->assertDatabaseHas('academic_terms', [
            'semester_no' => 1,
            'academic_year' => (int) date('Y'),
        ]);
        $scienceAdvisor = User::where('email', 'advisor.sci@uru.ac.th')->firstOrFail();
        $managementAdvisor = User::where('email', 'advisor.mgt@uru.ac.th')->firstOrFail();
        $scienceStudent = User::where('email', 'student.sci@uru.ac.th')->firstOrFail();
        $managementStudent = User::where('email', 'student.mgt@uru.ac.th')->firstOrFail();
        $this->assertNotSame($scienceAdvisor->faculty_id, $managementAdvisor->faculty_id);
        $this->assertNotSame($scienceStudent->studentProfile->program->faculty_id, $managementStudent->studentProfile->program->faculty_id);
        $this->assertEquals($scienceAdvisor->id, $scienceStudent->advisor_id);
        $this->assertEquals($managementAdvisor->id, $managementStudent->advisor_id);
    }
}

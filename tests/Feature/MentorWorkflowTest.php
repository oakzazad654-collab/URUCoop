<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Approval;
use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\CoopApplication;
use App\Models\Faculty;
use App\Models\Placement;
use App\Models\Program;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkPlan;
use App\Models\WorkPlanItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_mentor_reviews_work_plan_before_advisor_and_sees_only_assigned_placements()
    {
        $faculty = Faculty::create(['name' => 'คณะนิเทศงาน']);
        $program = Program::create([
            'program_code' => 'MENTOR-CS',
            'program_name' => 'หลักสูตรนิเทศงาน',
            'faculty_id' => $faculty->id,
            'status' => 'ACTIVE',
        ]);
        $advisor = User::create([
            'name' => 'อาจารย์ที่ปรึกษา',
            'email' => 'mentor-advisor@example.test',
            'password' => bcrypt('advisor-password'),
            'role' => 'advisor',
            'faculty_id' => $faculty->id,
        ]);
        Staff::create(['user_id' => $advisor->id, 'staff_code' => 'ADV-MENTOR-1', 'faculty_id' => $faculty->id]);
        $studentUser = User::create([
            'name' => 'นักศึกษาฝึกงาน',
            'email' => 'mentor-student@example.test',
            'password' => bcrypt('student-password'),
            'role' => 'student',
            'advisor_id' => $advisor->id,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'MENTOR-STUDENT-1',
            'program_id' => $program->program_id,
        ]);
        $company = Company::create(['name' => 'บริษัทฝึกงาน', 'status' => 'active']);
        $mentor = User::create([
            'name' => 'ผู้นิเทศงาน',
            'email' => 'mentor-user@example.test',
            'password' => bcrypt('mentor-password'),
            'role' => 'mentor',
            'status' => 'ACTIVE',
            'company_id' => $company->id,
        ]);
        $contact = CompanyContact::create([
            'company_id' => $company->id,
            'contact_type' => 'MENTOR',
            'first_name' => 'ผู้นิเทศ',
            'last_name' => 'งาน',
            'position_name' => 'หัวหน้างาน',
            'email' => $mentor->email,
            'status' => 'ACTIVE',
            'user_id' => $mentor->id,
        ]);
        $term = AcademicTerm::create(['semester_no' => 1, 'academic_year' => 2026, 'status' => 'OPEN']);
        $application = CoopApplication::create([
            'application_no' => 'APP-MENTOR-1',
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_date' => '2026-04-01',
            'application_status' => 'APPROVED',
        ]);
        $placement = Placement::create([
            'placement_no' => 'PLC-MENTOR-1',
            'application_id' => $application->application_id,
            'student_id' => $student->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'mentor_contact_id' => $contact->contact_id,
            'job_position' => 'ผู้ช่วยพัฒนา',
            'job_description' => 'พัฒนางานทดสอบ',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => 'ACCEPTED',
        ]);
        $plan = WorkPlan::create([
            'placement_id' => $placement->placement_id,
            'plan_title' => 'แผนฝึกงาน',
            'plan_status' => 'DRAFT',
        ]);
        WorkPlanItem::create([
            'work_plan_id' => $plan->work_plan_id,
            'sequence_no' => 1,
            'activity_name' => 'ปฐมนิเทศ',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-02',
        ]);

        $this->actingAs($studentUser)->post(route('workflow.work-plans.submit', $plan))
            ->assertRedirect(route('workflow.index'));
        $this->assertDatabaseHas('approvals', [
            'document_type' => 'WORK_PLAN',
            'document_id' => $plan->work_plan_id,
            'approver_id' => $mentor->id,
            'approver_role' => 'MENTOR',
            'approval_status' => 'PENDING',
        ]);
        $this->assertSame('PENDING_MENTOR', $plan->fresh()->plan_status);

        $this->actingAs($mentor)->get(route('mentor.dashboard'))
            ->assertOk()->assertSee('นักศึกษาฝึกงาน')->assertSee('แผนฝึกงาน');
        $this->actingAs($mentor)->post(route('workflow.approvals.action', Approval::where('approver_id', $mentor->id)->first()), [
            'action' => 'APPROVED',
        ])->assertRedirect(route('workflow.index'));
        $this->assertSame('PENDING_ADVISOR', $plan->fresh()->plan_status);
        $this->assertNotNull($plan->fresh()->mentor_confirmed_at);
        $this->assertDatabaseHas('approvals', [
            'document_type' => 'WORK_PLAN',
            'document_id' => $plan->work_plan_id,
            'approver_id' => $advisor->id,
            'approver_role' => 'ADVISOR',
            'approval_step' => 2,
            'approval_status' => 'PENDING',
        ]);
    }
}

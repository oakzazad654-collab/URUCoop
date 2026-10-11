<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\DocumentRecord;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Role;
use App\Models\LetterRequest;
use App\Models\Placement;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SupervisionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CooperativeEducationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_token_runs_application_submission_and_qualification_review()
    {
        $faculty = Faculty::create(['name' => 'คณะทดสอบ']);
        $studentRole = Role::create(['role_code' => 'STUDENT', 'role_name' => 'นักศึกษา']);
        $coordinatorRole = Role::create(['role_code' => 'COORDINATOR', 'role_name' => 'ผู้ประสานงาน']);
        $advisor = User::create([
            'name' => 'อาจารย์ผู้ประสานงาน',
            'email' => 'advisor-api@example.test',
            'password' => Hash::make('secret'),
            'role' => 'advisor',
            'role_id' => $coordinatorRole->role_id,
            'faculty_id' => $faculty->id,
        ]);
        $studentUser = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'student-api@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
            'role_id' => $studentRole->role_id,
            'advisor_id' => $advisor->id,
        ]);
        $program = Program::create([
            'program_code' => 'API-CS',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'API0001',
            'program_id' => $program->program_id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create([
            'name' => 'บริษัท API ทดสอบ',
            'company_name_th' => 'บริษัท API ทดสอบ',
            'company_code' => 'API-COMPANY',
        ]);

        $studentToken = $this->postJson('/api/auth/login', [
            'identifier' => $studentUser->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $this->assertSame(hash('sha256', $studentToken), $studentUser->fresh()->api_token_hash);

        $applicationId = $this->withToken($studentToken)->postJson('/api/applications', [
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'desired_position' => 'Developer Intern',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
        ])->assertCreated()->json('application_id');

        $this->withToken($studentToken)
            ->postJson('/api/applications/'.$applicationId.'/submit')
            ->assertOk()
            ->assertJsonPath('application_status', 'SUBMITTED');

        $advisorToken = $this->postJson('/api/auth/login', [
            'identifier' => $advisor->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        $this->withToken($advisorToken)
            ->postJson('/api/applications/'.$applicationId.'/review', [
                'academic_result' => true,
                'behavior_result' => true,
                'activity_result' => true,
                'health_result' => true,
                'result' => 'QUALIFIED',
                'comment' => 'ผ่านเกณฑ์',
            ])
            ->assertOk()
            ->assertJsonPath('application_status', 'APPROVED');

        $this->assertDatabaseHas('qualification_reviews', [
            'application_id' => $applicationId,
            'reviewer_id' => $advisor->id,
            'result' => 'QUALIFIED',
        ]);
        $this->assertDatabaseHas('approvals', [
            'document_type' => 'APPLICATION',
            'document_id' => $applicationId,
            'approval_status' => 'APPROVED',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'coop_applications',
            'record_id' => $applicationId,
            'action_name' => 'APPLICATION_REVIEWED',
        ]);
        $this->withToken($advisorToken)
            ->getJson('/api/reports/application-status')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'APPROVED');
        $this->withToken($advisorToken)
            ->get('/api/reports/application-status?format=csv')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->withToken($advisorToken)
            ->get('/api/reports/application-status?format=pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $application = CoopApplication::findOrFail($applicationId);
        $placement = Placement::create([
            'placement_no' => 'PLC-API-0001',
            'application_id' => $application->application_id,
            'student_id' => $application->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'job_position' => 'Developer Intern',
            'job_description' => 'พัฒนาระบบทดสอบ',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => 'ACCEPTED',
            'placement_status' => 'WORKING',
        ]);
        $workPlanId = $this->withToken($studentToken)->postJson('/api/work-plans', [
            'placement_id' => $placement->placement_id,
            'plan_title' => 'แผนงานทดสอบ',
            'items' => [[
                'activity_name' => 'พัฒนาระบบ',
                'start_date' => '2026-06-01',
                'end_date' => '2026-09-30',
                'weight_percent' => 100,
            ]],
        ])->assertCreated()->json('work_plan_id');
        $this->withToken($studentToken)
            ->postJson('/api/work-plans/'.$workPlanId.'/submit')
            ->assertOk()->assertJsonPath('plan_status', 'SUBMITTED');
        $workPlanApproval = \App\Models\Approval::where('document_type', 'WORK_PLAN')
            ->where('document_id', $workPlanId)->firstOrFail();
        $this->withToken($advisorToken)->postJson('/api/approvals/'.$workPlanApproval->approval_id.'/action', [
            'action' => 'APPROVED',
        ])->assertOk();
        $this->assertDatabaseHas('work_plans', ['work_plan_id' => $workPlanId, 'plan_status' => 'APPROVED']);

        $outlineId = $this->withToken($studentToken)->postJson('/api/report-outlines', [
            'placement_id' => $placement->placement_id,
            'report_title_th' => 'โครงร่างรายงานทดสอบ',
            'objectives' => 'ทดสอบ workflow',
        ])->assertCreated()->json('outline_id');
        $this->withToken($studentToken)
            ->postJson('/api/report-outlines/'.$outlineId.'/submit')
            ->assertOk()->assertJsonPath('outline_status', 'SUBMITTED');
        $outlineApproval = \App\Models\Approval::where('document_type', 'REPORT_OUTLINE')
            ->where('document_id', $outlineId)->firstOrFail();
        $this->withToken($advisorToken)->postJson('/api/approvals/'.$outlineApproval->approval_id.'/action', [
            'action' => 'APPROVED',
        ])->assertOk();
        $this->assertDatabaseHas('report_outlines', ['outline_id' => $outlineId, 'outline_status' => 'APPROVED']);
    }

    public function test_multi_student_letter_request_requires_both_approval_stages()
    {
        $faculty = Faculty::create(['name' => 'คณะทดสอบ']);
        $studentRole = Role::create(['role_code' => 'STUDENT', 'role_name' => 'นักศึกษา']);
        $advisor = User::create([
            'name' => 'อาจารย์ประสานงาน',
            'email' => 'advisor-letter@example.test',
            'password' => Hash::make('secret'),
            'role' => 'advisor',
            'faculty_id' => $faculty->id,
        ]);
        $studentUsers = collect([1, 2])->map(function ($number) use ($studentRole, $advisor) {
            return User::create([
                'name' => 'นักศึกษา '.$number,
                'email' => 'student-letter-'.$number.'@example.test',
                'password' => Hash::make('secret'),
                'role' => 'student',
                'role_id' => $studentRole->role_id,
                'advisor_id' => $advisor->id,
            ]);
        });
        $program = Program::create([
            'program_code' => 'LETTER-CS',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create([
            'name' => 'บริษัทหนังสือทดสอบ',
            'company_name_th' => 'บริษัทหนังสือทดสอบ',
            'company_code' => 'LETTER-COMPANY',
        ]);
        $applications = $studentUsers->map(function ($user, $index) use ($program, $term, $company) {
            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => 'LETTER000'.($index + 1),
                'program_id' => $program->program_id,
            ]);
            return CoopApplication::create([
                'application_no' => 'APP-LETTER-000'.($index + 1),
                'student_id' => $student->student_id,
                'term_id' => $term->term_id,
                'preferred_company_id' => $company->id,
                'work_start_date' => '2026-06-01',
                'work_end_date' => '2026-09-30',
                'application_date' => '2026-04-01',
                'qualification_result' => 'QUALIFIED',
                'application_status' => 'APPROVED',
            ]);
        });

        $advisorToken = $this->postJson('/api/auth/login', [
            'identifier' => $advisor->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $letterRequestId = $this->withToken($advisorToken)->postJson('/api/letter-requests', [
            'application_ids' => $applications->pluck('application_id')->all(),
            'receiver_name' => 'ผู้รับหนังสือทดสอบ',
        ])->assertCreated()->json('request_id');

        $this->assertDatabaseCount('letter_request_students', 2);
        $studentToken = $this->postJson('/api/auth/login', [
            'identifier' => $studentUsers->first()->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $this->withToken($studentToken)->getJson('/api/letter-requests')
            ->assertOk()->assertJsonCount(1, 'data.0.students')
            ->assertJsonPath('data.0.students.0.student_code', 'LETTER0001');
        $approval = \App\Models\Approval::where('document_type', 'LETTER_REQUEST')
            ->where('document_id', $letterRequestId)->firstOrFail();
        $this->withToken($advisorToken)->postJson('/api/approvals/'.$approval->approval_id.'/action', [
            'action' => 'APPROVED',
            'comment' => 'เห็นชอบ',
        ])->assertOk();
        $this->assertSame('APPROVED', LetterRequest::findOrFail($letterRequestId)->coordinator_result);

        $officer = User::create([
            'name' => 'เจ้าหน้าที่',
            'email' => 'officer-letter@example.test',
            'password' => Hash::make('secret'),
            'role' => 'officer',
        ]);
        $officerToken = $this->postJson('/api/auth/login', [
            'identifier' => $officer->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $this->withToken($officerToken)->postJson('/api/letter-requests/'.$letterRequestId.'/check-documents', [
            'document_check_result' => 'COMPLETE',
        ])->assertOk()->assertJsonPath('request_status', 'LETTER_ISSUED');
    }

    public function test_supervision_request_saves_team_schedule_and_result()
    {
        $faculty = Faculty::create(['name' => 'คณะนิเทศทดสอบ']);
        $supervisor = User::create([
            'name' => 'อาจารย์นิเทศ',
            'email' => 'supervisor-api@example.test',
            'password' => Hash::make('secret'),
            'role' => 'advisor',
            'faculty_id' => $faculty->id,
        ]);
        $executive = User::create([
            'name' => 'ผู้บริหาร',
            'email' => 'executive-api@example.test',
            'password' => Hash::make('secret'),
            'role' => 'executive',
        ]);
        $staff = Staff::create([
            'user_id' => $supervisor->id,
            'staff_code' => 'SUP-API-01',
            'faculty_id' => $faculty->id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create([
            'name' => 'บริษัทนิเทศทดสอบ',
            'company_name_th' => 'บริษัทนิเทศทดสอบ',
            'company_code' => 'SUP-COMPANY',
        ]);
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $supervisor->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        $response = $this->withToken($token)->postJson('/api/supervision-requests', [
            'term_id' => $term->term_id,
            'faculty_id' => $faculty->id,
            'purpose' => 'นิเทศติดตามผล',
            'members' => [['staff_id' => $staff->staff_id, 'member_role' => 'LEADER']],
            'schedules' => [[
                'company_id' => $company->id,
                'supervision_date' => '2026-10-20',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'province' => 'อุตรดิตถ์',
            ]],
        ])->assertCreated();
        $requestId = $response->json('data.supervision_request_id');
        $scheduleId = $response->json('data.schedules.0.schedule_id');
        $this->assertSame([], $response->json('warnings'));
        $this->assertDatabaseHas('supervision_requests', [
            'supervision_request_id' => $requestId,
            'request_status' => 'SUBMITTED',
        ]);
        $approval = \App\Models\Approval::where('document_type', 'SUPERVISION_REQUEST')
            ->where('document_id', $requestId)->firstOrFail();
        $executiveToken = $this->postJson('/api/auth/login', [
            'identifier' => $executive->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $this->withToken($executiveToken)->postJson('/api/approvals/'.$approval->approval_id.'/action', [
            'action' => 'APPROVED',
            'comment' => 'อนุมัติคำขอนิเทศ',
        ])->assertOk();
        $this->assertDatabaseHas('supervision_requests', [
            'supervision_request_id' => $requestId,
            'request_status' => 'APPROVED',
        ]);

        $this->withToken($token)->postJson('/api/supervision-schedules/'.$scheduleId.'/result', [
            'performance_summary' => 'ปฏิบัติงานตามแผน',
            'result' => 'GOOD',
            'visited_at' => now()->toDateTimeString(),
        ])->assertCreated();
        $this->assertDatabaseHas('supervision_schedules', [
            'schedule_id' => $scheduleId,
            'schedule_status' => 'COMPLETED',
        ]);
        $this->assertDatabaseHas('supervision_results', [
            'schedule_id' => $scheduleId,
            'supervisor_id' => $supervisor->id,
            'result' => 'GOOD',
        ]);
        $this->assertTrue(SupervisionRequest::findOrFail($requestId)->schedules()->exists());
    }

    public function test_private_generic_document_cannot_be_downloaded_by_another_user()
    {
        $owner = User::create([
            'name' => 'เจ้าของเอกสาร',
            'email' => 'document-owner@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $otherUser = User::create([
            'name' => 'ผู้ใช้อื่น',
            'email' => 'other-document-user@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $document = DocumentRecord::create([
            'owner_user_id' => $owner->id,
            'related_type' => 'OTHER',
            'related_id' => 1,
            'document_name' => 'เอกสารส่วนตัว',
            'file_name' => 'randomized.pdf',
            'file_path' => 'documents/'.$owner->id.'/randomized.pdf',
            'mime_type' => 'application/pdf',
        ]);
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $otherUser->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->get('/api/documents/'.$document->document_id.'/download')
            ->assertForbidden();
    }

    public function test_student_cannot_apply_to_a_closed_or_not_yet_open_term()
    {
        $faculty = Faculty::create(['name' => 'คณะทดสอบช่วงรับสมัคร']);
        $studentUser = User::create([
            'name' => 'นักศึกษาทดสอบ',
            'email' => 'term-window-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $program = Program::create([
            'program_code' => 'TERM-WINDOW',
            'program_name' => 'หลักสูตรทดสอบ',
            'faculty_id' => $faculty->id,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'TERM-WINDOW-01',
            'program_id' => $program->program_id,
        ]);
        $company = Company::create([
            'name' => 'บริษัททดสอบช่วงรับสมัคร',
            'company_name_th' => 'บริษัททดสอบช่วงรับสมัคร',
            'company_code' => 'TERM-WINDOW',
        ]);
        $closedTerm = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2027,
            'status' => 'CLOSED',
        ]);
        $futureTerm = AcademicTerm::create([
            'semester_no' => 2,
            'academic_year' => 2027,
            'registration_start_date' => now()->addDays(10)->toDateString(),
            'status' => 'OPEN',
        ]);
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $studentUser->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        foreach ([$closedTerm, $futureTerm] as $term) {
            $this->withToken($token)->postJson('/api/applications', [
                'term_id' => $term->term_id,
                'preferred_company_id' => $company->id,
                'work_start_date' => now()->addMonths(2)->toDateString(),
                'work_end_date' => now()->addMonths(5)->toDateString(),
            ])->assertStatus(422);
        }

        $this->assertDatabaseMissing('coop_applications', [
            'student_id' => $student->student_id,
        ]);
    }
}

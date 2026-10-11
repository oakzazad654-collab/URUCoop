<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Approval;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\CwieDocument;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Placement;
use App\Models\ReportOutline;
use App\Models\Student;
use App\Models\SupervisionRequest;
use App\Models\User;
use App\Models\WorkPlan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DataIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_advisor_cannot_list_or_review_a_student_from_another_faculty()
    {
        $science = Faculty::create(['name' => 'คณะวิทยาศาสตร์']);
        $management = Faculty::create(['name' => 'คณะวิทยาการจัดการ']);
        $scienceProgram = Program::create([
            'program_code' => 'ISO-SCI',
            'program_name' => 'วิทยาศาสตร์',
            'faculty_id' => $science->id,
        ]);
        $managementProgram = Program::create([
            'program_code' => 'ISO-MGT',
            'program_name' => 'บริหารธุรกิจ',
            'faculty_id' => $management->id,
        ]);
        $advisor = User::create([
            'name' => 'อาจารย์คณะวิทยาศาสตร์',
            'email' => 'science-advisor@example.test',
            'password' => Hash::make('secret'),
            'role' => 'advisor',
            'faculty_id' => $science->id,
        ]);
        $studentUser = User::create([
            'name' => 'นักศึกษาคณะบริหาร',
            'email' => 'management-student@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
            'advisor_id' => $advisor->id,
            'faculty_id' => $management->id,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'ISO-MGT-01',
            'program_id' => $managementProgram->program_id,
        ]);
        $term = AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2026,
            'status' => 'OPEN',
        ]);
        $company = Company::create(['name' => 'บริษัททดสอบ isolation']);
        $application = CoopApplication::create([
            'application_no' => 'APP-ISO-MGT-01',
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_date' => '2026-04-01',
            'application_status' => 'SUBMITTED',
        ]);
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $advisor->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/applications')->assertOk()->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/reports/application-status')
            ->assertOk()->assertJsonCount(0, 'data');

        $this->withToken($token)->postJson('/api/applications/'.$application->application_id.'/review', [
            'academic_result' => true,
            'behavior_result' => true,
            'activity_result' => true,
            'health_result' => true,
            'result' => 'QUALIFIED',
        ])->assertForbidden();

        $placement = Placement::create([
            'placement_no' => 'PLC-ISO-MGT-01',
            'application_id' => $application->application_id,
            'student_id' => $student->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'job_position' => 'Trainee',
            'job_description' => 'Cross faculty data',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => 'ACCEPTED',
        ]);
        WorkPlan::create([
            'placement_id' => $placement->placement_id,
            'plan_title' => 'แผนของคณะอื่น',
        ]);
        ReportOutline::create([
            'placement_id' => $placement->placement_id,
            'report_title_th' => 'รายงานของคณะอื่น',
        ]);
        $this->withToken($token)->getJson('/api/placements')->assertOk()->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/work-plans')->assertOk()->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/report-outlines')->assertOk()->assertJsonCount(0, 'data');
        SupervisionRequest::create([
            'request_no' => 'SUP-ISO-MGT-01',
            'term_id' => $term->term_id,
            'requester_id' => $studentUser->id,
            'faculty_id' => $management->id,
            'request_date' => '2026-04-01',
            'request_status' => 'SUBMITTED',
        ]);
        $this->withToken($token)->getJson('/api/supervision-requests')->assertOk()->assertJsonCount(0, 'data');

        $approval = Approval::create([
            'document_type' => 'APPLICATION',
            'document_id' => $application->application_id,
            'approval_step' => 1,
            'approver_id' => $advisor->id,
            'approval_status' => 'PENDING',
        ]);
        $this->withToken($token)->postJson('/api/approvals/'.$approval->approval_id.'/action', [
            'action' => 'REJECTED',
            'comment' => 'cross-faculty attempt',
        ])->assertForbidden();

        $legacyDocument = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'เอกสารคณะบริหารที่ไม่ควรเห็น',
            'user_id' => $studentUser->id,
            'status' => 'pending_advisor',
            'student_id' => $studentUser->student_number,
            'data' => ['student_name' => $studentUser->name],
        ]);
        $this->actingAs($advisor)->get('/advisor/approvals')
            ->assertOk()->assertDontSee('เอกสารคณะบริหารที่ไม่ควรเห็น');
        $this->actingAs($advisor)->get('/advisor/approvals/cwie-01/'.$legacyDocument->id)
            ->assertForbidden();

        $officer = User::create([
            'name' => 'เจ้าหน้าที่คณะวิทยาศาสตร์',
            'email' => 'science-officer@example.test',
            'password' => Hash::make('secret'),
            'role' => 'officer',
            'faculty_id' => $science->id,
        ]);
        $officerToken = $this->postJson('/api/auth/login', [
            'identifier' => $officer->email,
            'password' => 'secret',
        ])->assertOk()->json('token');
        $this->withToken($officerToken)->getJson('/api/students')
            ->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($officer)->get('/officer/users/'.$studentUser->id.'/edit')
            ->assertForbidden();
        $this->actingAs($officer)->get('/officer/approvals')
            ->assertOk()
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 0 && $stats['pending'] === 0 && $stats['companies'] === null;
            });
    }

    public function test_student_token_cannot_read_or_edit_another_students_profile()
    {
        $faculty = Faculty::create(['name' => 'คณะนักศึกษา isolation']);
        $program = Program::create([
            'program_code' => 'ISO-STU',
            'program_name' => 'หลักสูตร isolation',
            'faculty_id' => $faculty->id,
        ]);
        $users = collect(['owner', 'other'])->map(function ($suffix) {
            return User::create([
                'name' => 'Student '.$suffix,
                'email' => $suffix.'-owner@example.test',
                'password' => Hash::make('secret'),
                'role' => 'student',
            ]);
        });
        $students = $users->map(function ($user, $index) use ($program) {
            return Student::create([
                'user_id' => $user->id,
                'student_code' => 'ISO-STU-'.($index + 1),
                'program_id' => $program->program_id,
                'current_address' => 'private address '.$index,
            ]);
        });
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $users->first()->email,
            'password' => 'secret',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/students')
            ->assertOk()->assertJsonCount(1, 'data');
        $this->withToken($token)->getJson('/api/students/'.$students->first()->student_id)
            ->assertOk()->assertJsonPath('student_code', 'ISO-STU-1');
        $this->withToken($token)->getJson('/api/students/'.$students->last()->student_id)
            ->assertNotFound();
        $this->withToken($token)->patchJson('/api/students/'.$students->last()->student_id, [
            'current_address' => 'attempted takeover',
        ])->assertForbidden();
        $this->assertDatabaseHas('students', [
            'student_id' => $students->last()->student_id,
            'current_address' => 'private address 1',
        ]);
        $otherDocument = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'เอกสารของนักศึกษาอีกคน',
            'user_id' => $users->last()->id,
            'status' => 'draft',
            'data' => ['student_name' => 'Student other'],
        ]);
        $this->actingAs($users->first())->get('/student/cwie/cwie-01/'.$otherDocument->id)
            ->assertForbidden();
        $identity = $this->withToken($token)->getJson('/api/user')->assertOk();
        $this->assertArrayNotHasKey('api_token_hash', $identity->json());
        $this->assertArrayNotHasKey('password_hash', $identity->json());
    }

    public function test_seeded_supervisor_token_only_lists_its_faculty_students()
    {
        $this->seed(DatabaseSeeder::class);
        $supervisor = User::where('email', 'supervisor.sci@uru.ac.th')->firstOrFail();
        $password = env('URU_COOP_DEMO_PASSWORD', 'password');
        $token = $this->postJson('/api/auth/login', [
            'identifier' => $supervisor->email,
            'password' => $password,
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/students')
            ->assertOk()
            ->assertJsonFragment(['student_code' => '65010001'])
            ->assertJsonMissing(['student_code' => '65020001']);

        $executive = User::where('email', 'executive@uru.ac.th')->firstOrFail();
        $executiveToken = $this->postJson('/api/auth/login', [
            'identifier' => $executive->email,
            'password' => $password,
        ])->assertOk()->json('token');
        $this->withToken($executiveToken)->getJson('/api/students')
            ->assertOk()
            ->assertJsonFragment(['student_code' => '65010001'])
            ->assertJsonFragment(['student_code' => '65020001']);
    }
}

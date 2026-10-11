<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\CoopApplication;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Placement;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_accounts_only_see_their_own_placements_and_can_decide_once()
    {
        [$companyA, $accountA] = $this->createCompanyAccount('A');
        [$companyB, $accountB] = $this->createCompanyAccount('B');
        $placementA = $this->createPlacement($companyA, 'A');
        $placementB = $this->createPlacement($companyB, 'B');

        $this->post('/login', [
            'email' => $accountA->email,
            'password' => 'company-secret',
        ])->assertRedirect('/company/dashboard');

        $this->get('/company/dashboard')
            ->assertOk()
            ->assertSee('นักศึกษา A')
            ->assertDontSee('นักศึกษา B');
        $this->get('/admin/users')->assertForbidden();

        $token = $this->postJson('/api/auth/login', [
            'identifier' => $accountA->email,
            'password' => 'company-secret',
        ])->assertOk()->json('token');
        $this->withToken($token)->getJson('/api/companies')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $companyA->name])
            ->assertJsonMissing(['name' => $companyB->name]);
        $this->withToken($token)->getJson('/api/placements')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['placement_id' => $placementA->placement_id])
            ->assertJsonMissing(['placement_id' => $placementB->placement_id]);

        $this->actingAs($accountA)
            ->post('/company/placements/'.$placementA->placement_id.'/acceptance', [
                'acceptance_status' => 'ACCEPTED',
            ])->assertRedirect('/company/dashboard');
        $this->assertDatabaseHas('placements', [
            'placement_id' => $placementA->placement_id,
            'acceptance_status' => 'ACCEPTED',
        ]);

        $this->actingAs($accountA)
            ->post('/company/placements/'.$placementB->placement_id.'/acceptance', [
                'acceptance_status' => 'REJECTED',
            ])->assertForbidden();
        $this->assertDatabaseHas('placements', [
            'placement_id' => $placementB->placement_id,
            'acceptance_status' => 'PENDING',
        ]);

        $this->actingAs($accountA)
            ->post('/company/placements/'.$placementA->placement_id.'/acceptance', [
                'acceptance_status' => 'REJECTED',
            ])->assertStatus(422);
        $this->assertDatabaseHas('placements', [
            'placement_id' => $placementA->placement_id,
            'acceptance_status' => 'ACCEPTED',
        ]);
    }

    public function test_company_can_view_only_its_own_student_placement_detail()
    {
        [$companyA, $accountA] = $this->createCompanyAccount('DETAIL-A');
        [$companyB] = $this->createCompanyAccount('DETAIL-B');
        $placementA = $this->createPlacement($companyA, 'DETAIL-A');
        $placementB = $this->createPlacement($companyB, 'DETAIL-B');

        $this->actingAs($accountA)
            ->get(route('company.placements.show', $placementA->placement_id))
            ->assertOk()
            ->assertSee('PORTAL-DETAIL-A')
            ->assertDontSee('PORTAL-DETAIL-B');

        $this->actingAs($accountA)
            ->get(route('company.placements.show', $placementB->placement_id))
            ->assertNotFound();
    }

    private function createCompanyAccount($suffix)
    {
        $company = Company::create([
            'name' => 'บริษัท '.$suffix,
            'company_code' => 'PORTAL-'.$suffix,
            'status' => 'active',
        ]);
        $user = User::create([
            'name' => 'ผู้ประสานงาน '.$suffix,
            'email' => 'company-'.$suffix.'@example.test',
            'password' => Hash::make('company-secret'),
            'role' => 'company',
            'company_id' => $company->id,
            'status' => 'ACTIVE',
        ]);

        return [$company, $user];
    }

    private function createPlacement(Company $company, $suffix)
    {
        $faculty = Faculty::firstOrCreate(['name' => 'คณะทดสอบ '.$suffix]);
        $program = Program::create([
            'program_code' => 'PORTAL-'.$suffix,
            'program_name' => 'หลักสูตรทดสอบ '.$suffix,
            'faculty_id' => $faculty->id,
        ]);
        $studentUser = User::create([
            'name' => 'นักศึกษา '.$suffix,
            'email' => 'portal-student-'.$suffix.'@example.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'PORTAL-'.$suffix,
            'program_id' => $program->program_id,
        ]);
        $term = AcademicTerm::firstOrCreate([
            'semester_no' => 1,
            'academic_year' => 2026,
        ], [
            'status' => 'OPEN',
        ]);
        $application = CoopApplication::create([
            'application_no' => 'PORTAL-'.$suffix,
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'application_date' => '2026-01-01',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_status' => 'APPROVED',
            'qualification_result' => 'QUALIFIED',
        ]);

        return Placement::create([
            'placement_no' => 'PLACEMENT-'.$suffix,
            'application_id' => $application->application_id,
            'student_id' => $student->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'job_position' => 'นักศึกษาฝึกงาน',
            'job_description' => 'งานทดสอบ',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => 'PENDING',
            'placement_status' => 'PREPARING',
        ]);
    }
}

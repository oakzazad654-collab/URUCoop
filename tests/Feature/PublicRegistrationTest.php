<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_with_academic_and_personal_information_and_login_immediately()
    {
        [$faculty, $department, $program] = $this->academicData();

        $this->get(route('register'))->assertOk()->assertSee('ละติจูด');
        $response = $this->post(route('register.store'), [
            'role' => 'student',
            'name' => 'นักศึกษาสมัครใหม่',
            'email' => 'new-student@example.test',
            'phone' => '0812345678',
            'password' => 'student-password',
            'password_confirmation' => 'student-password',
            'student_number' => 'STU-10001',
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'program_id' => $program->program_id,
            'current_address' => 'ที่อยู่ทดสอบ',
            'emergency_contact_name' => 'ผู้ติดต่อฉุกเฉิน',
            'emergency_contact_phone' => '0899999999',
            'cumulative_gpa' => '3.25',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated();
        $user = User::where('email', 'new-student@example.test')->firstOrFail();
        $this->assertSame('ACTIVE', $user->status);
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'student_code' => 'STU-10001',
            'program_id' => $program->program_id,
            'current_address' => 'ที่อยู่ทดสอบ',
            'emergency_contact_name' => 'ผู้ติดต่อฉุกเฉิน',
        ]);
        $this->assertInstanceOf(Student::class, $user->studentProfile);
    }

    public function test_non_student_roles_wait_for_admin_approval_before_login()
    {
        [$faculty, $department, $program] = $this->academicData();
        $this->post(route('register.store'), [
            'role' => 'advisor',
            'name' => 'อาจารย์สมัครใหม่',
            'email' => 'new-advisor@example.test',
            'phone' => '0812345678',
            'password' => 'advisor-password',
            'password_confirmation' => 'advisor-password',
            'faculty_id' => $faculty->id,
            'department_id' => $department->id,
            'program_id' => $program->program_id,
            'staff_code' => 'ADV-10001',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $advisor = User::where('email', 'new-advisor@example.test')->firstOrFail();
        $this->assertSame('PENDING', $advisor->status);
        $this->assertDatabaseHas('staff', ['user_id' => $advisor->id, 'staff_code' => 'ADV-10001']);

        $this->post(route('login'), ['email' => $advisor->email, 'password' => 'advisor-password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_company_registration_requires_coordinates_and_admin_approval_activates_company_account()
    {
        $payload = [
            'role' => 'company',
            'name' => 'ผู้ประสานงานบริษัท',
            'email' => 'company-user@example.test',
            'phone' => '055123456',
            'password' => 'company-password',
            'password_confirmation' => 'company-password',
            'company_name' => 'บริษัทพิกัด จำกัด',
            'business_type' => 'เทคโนโลยี',
            'address' => 'จังหวัดอุตรดิตถ์',
            'contact_person' => 'ผู้ประสานงาน',
        ];
        $this->from(route('register'))->post(route('register.store'), $payload)
            ->assertRedirect(route('register'))->assertSessionHasErrors(['latitude', 'longitude']);

        $payload['latitude'] = '17.6200';
        $payload['longitude'] = '100.1000';
        $this->post(route('register.store'), $payload)
            ->assertRedirect(route('login'))->assertSessionHas('status');
        $companyUser = User::where('email', 'company-user@example.test')->firstOrFail();
        $this->assertSame('PENDING', $companyUser->status);
        $this->assertDatabaseHas('companies', [
            'id' => $companyUser->company_id,
            'latitude' => '17.62000000',
            'longitude' => '100.10000000',
            'status' => 'pending',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'registration-admin@example.test',
            'password' => bcrypt('admin-password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin)->post(route('admin.registrations.approve', $companyUser))
            ->assertRedirect(route('admin.registrations.index'));
        $this->assertDatabaseHas('users', ['id' => $companyUser->id, 'status' => 'ACTIVE']);
        $this->assertDatabaseHas('companies', ['id' => $companyUser->company_id, 'status' => 'active']);
    }

    public function test_mentor_account_is_linked_to_company_contact_and_requires_admin_approval()
    {
        $company = Company::create([
            'name' => 'บริษัทผู้รับนักศึกษา',
            'status' => 'active',
        ]);
        $this->post(route('register.store'), [
            'role' => 'mentor',
            'name' => 'ผู้นิเทศ ทดสอบ',
            'email' => 'mentor-new@example.test',
            'phone' => '0811111111',
            'password' => 'mentor-password',
            'password_confirmation' => 'mentor-password',
            'company_id' => $company->id,
            'position_name' => 'หัวหน้างาน',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $mentor = User::where('email', 'mentor-new@example.test')->firstOrFail();
        $this->assertSame('PENDING', $mentor->status);
        $contact = CompanyContact::where('user_id', $mentor->id)->firstOrFail();
        $this->assertSame('PENDING', $contact->status);
        $this->assertSame($company->id, $contact->company_id);
    }

    private function academicData()
    {
        $faculty = Faculty::create(['name' => 'คณะลงทะเบียน']);
        $department = Department::create(['faculty_id' => $faculty->id, 'name' => 'สาขาลงทะเบียน']);
        $program = Program::create([
            'program_code' => 'REG-1',
            'program_name' => 'หลักสูตรลงทะเบียน',
            'faculty_id' => $faculty->id,
            'status' => 'ACTIVE',
        ]);

        return [$faculty, $department, $program];
    }
}

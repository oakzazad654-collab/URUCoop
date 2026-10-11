<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DataIsolationDemoSeeder extends Seeder
{
    public function run()
    {
        $roles = DB::table('roles')->pluck('role_id', 'role_code');
        $password = Hash::make(env('URU_COOP_DEMO_PASSWORD', 'password'));

        $faculties = [
            'SCI' => [
                'name' => 'คณะวิทยาศาสตร์และเทคโนโลยี',
                'program_code' => 'SCI-CS',
                'program_name' => 'วิทยาการคอมพิวเตอร์',
                'department_name' => 'สาขาวิทยาการคอมพิวเตอร์',
            ],
            'MGT' => [
                'name' => 'คณะวิทยาการจัดการ',
                'program_code' => 'MGT-BA',
                'program_name' => 'บริหารธุรกิจ',
                'department_name' => 'สาขาบริหารธุรกิจ',
            ],
        ];

        foreach ($faculties as $code => $details) {
            $faculty = Faculty::firstOrCreate(['name' => $details['name']]);
            DB::table('faculties')->where('id', $faculty->id)->update([
                'faculty_code' => $code,
                'faculty_name' => $details['name'],
                'status' => 'ACTIVE',
            ]);
            $department = Department::firstOrCreate([
                'faculty_id' => $faculty->id,
                'name' => $details['department_name'],
            ]);
            $program = Program::updateOrCreate(
                ['program_code' => $details['program_code']],
                [
                    'program_name' => $details['program_name'],
                    'faculty_id' => $faculty->id,
                    'status' => 'ACTIVE',
                ]
            );
            $faculties[$code]['faculty_id'] = $faculty->id;
            $faculties[$code]['department_id'] = $department->id;
            $faculties[$code]['program_id'] = $program->program_id;
        }

        $accounts = [
            'advisor.sci@uru.ac.th' => [
                'name' => 'อาจารย์ผู้ประสานงาน SCI', 'role' => 'advisor', 'role_code' => 'COORDINATOR',
                'username' => 'advisor_sci', 'faculty' => 'SCI', 'staff_code' => 'DEMO-SCI-ADV',
            ],
            'advisor.mgt@uru.ac.th' => [
                'name' => 'อาจารย์ผู้ประสานงาน MGT', 'role' => 'advisor', 'role_code' => 'COORDINATOR',
                'username' => 'advisor_mgt', 'faculty' => 'MGT', 'staff_code' => 'DEMO-MGT-ADV',
            ],
            'supervisor.sci@uru.ac.th' => [
                'name' => 'อาจารย์นิเทศ SCI', 'role' => 'supervisor', 'role_code' => 'SUPERVISOR',
                'username' => 'supervisor_sci', 'faculty' => 'SCI', 'staff_code' => 'DEMO-SCI-SUP',
            ],
            'officer.sci@uru.ac.th' => [
                'name' => 'เจ้าหน้าที่ประจำคณะ SCI', 'role' => 'officer', 'role_code' => 'OFFICER',
                'username' => 'officer_sci', 'faculty' => 'SCI', 'staff_code' => 'DEMO-SCI-OFF',
            ],
            'executive@uru.ac.th' => [
                'name' => 'ผู้บริหารตัวอย่าง', 'role' => 'executive', 'role_code' => 'EXECUTIVE',
                'username' => 'executive_demo', 'faculty' => null, 'staff_code' => 'DEMO-EXEC',
            ],
        ];

        foreach ($accounts as $email => $details) {
            $faculty = $details['faculty'] ? $faculties[$details['faculty']] : null;
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $details['name'],
                    'password' => $password,
                    'role' => $details['role'],
                    'role_id' => $roles[$details['role_code']] ?? null,
                    'username' => $details['username'],
                    'first_name' => $details['name'],
                    'last_name' => '',
                    'faculty_id' => $faculty['faculty_id'] ?? null,
                    'department_id' => $faculty['department_id'] ?? null,
                    'status' => 'ACTIVE',
                ]
            );

            Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_code' => $details['staff_code'],
                    'faculty_id' => $faculty['faculty_id'] ?? null,
                    'program_id' => $faculty['program_id'] ?? null,
                ]
            );
        }

        $studentAccounts = [
            'student.sci@uru.ac.th' => [
                'name' => 'สมชาย วิทย์ดี', 'username' => '65010001', 'code' => '65010001',
                'faculty' => 'SCI', 'advisor_email' => 'advisor.sci@uru.ac.th',
            ],
            'student.mgt@uru.ac.th' => [
                'name' => 'สุดา บริหารดี', 'username' => '65020001', 'code' => '65020001',
                'faculty' => 'MGT', 'advisor_email' => 'advisor.mgt@uru.ac.th',
            ],
        ];

        foreach ($studentAccounts as $email => $details) {
            $faculty = $faculties[$details['faculty']];
            $advisor = User::where('email', $details['advisor_email'])->firstOrFail();
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $details['name'],
                    'password' => $password,
                    'role' => 'student',
                    'role_id' => $roles['STUDENT'] ?? null,
                    'username' => $details['username'],
                    'student_number' => $details['code'],
                    'first_name' => $details['name'],
                    'last_name' => '',
                    'advisor_id' => $advisor->id,
                    'faculty_id' => $faculty['faculty_id'],
                    'department_id' => $faculty['department_id'],
                    'status' => 'ACTIVE',
                ]
            );

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_code' => $details['code'],
                    'program_id' => $faculty['program_id'],
                    'student_status' => 'ELIGIBLE',
                    'current_address' => 'ที่อยู่ตัวอย่าง '.$faculty['name'],
                    'emergency_contact_name' => 'ผู้ติดต่อฉุกเฉินตัวอย่าง',
                    'emergency_contact_relation' => 'ผู้ปกครอง',
                    'emergency_contact_phone' => '0800000000',
                ]
            );
        }
    }
}

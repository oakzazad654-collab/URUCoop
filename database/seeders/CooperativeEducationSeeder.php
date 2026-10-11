<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CooperativeEducationSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'ADMIN' => 'ผู้ดูแลระบบ',
            'STUDENT' => 'นักศึกษา',
            'COORDINATOR' => 'อาจารย์ผู้ประสานงาน',
            'SUPERVISOR' => 'อาจารย์นิเทศ',
            'MENTOR' => 'ผู้นิเทศงานสถานประกอบการ',
            'OFFICER' => 'เจ้าหน้าที่สหกิจศึกษา',
            'EXECUTIVE' => 'ผู้บริหาร',
            'COMPANY' => 'สถานประกอบการ',
        ];

        foreach ($roles as $code => $name) {
            DB::table('roles')->updateOrInsert(
                ['role_code' => $code],
                ['role_name' => $name, 'description' => null]
            );
        }

        $roleIds = DB::table('roles')->pluck('role_id', 'role_code');
        $legacyRoleMap = [
            'admin' => 'ADMIN',
            'student' => 'STUDENT',
            'advisor' => 'COORDINATOR',
            'officer' => 'OFFICER',
        ];

        foreach (DB::table('faculties')->get() as $faculty) {
            DB::table('faculties')->where('id', $faculty->id)->update([
                'faculty_code' => 'FAC-'.str_pad($faculty->id, 4, '0', STR_PAD_LEFT),
                'faculty_name' => $faculty->name,
            ]);

            $department = DB::table('departments')->where('faculty_id', $faculty->id)->value('name');
            $programCode = 'LEGACY-'.(int) $faculty->id;
            DB::table('programs')->updateOrInsert(
                ['program_code' => $programCode],
                [
                    'program_name' => $department ?: $faculty->name.' หลักสูตรกลาง',
                    'faculty_id' => $faculty->id,
                    'status' => 'ACTIVE',
                ]
            );
        }

        $programIds = DB::table('programs')->orderBy('program_id')->pluck('program_id', 'faculty_id');
        $termYear = (int) date('Y');
        DB::table('academic_terms')->updateOrInsert(
            ['semester_no' => 1, 'academic_year' => $termYear],
            ['status' => 'PLANNED']
        );

        foreach (DB::table('users')->get() as $user) {
            $roleCode = $legacyRoleMap[$user->role] ?? null;
            if (!$roleCode || !isset($roleIds[$roleCode])) {
                continue;
            }

            $username = $user->student_number ?: $user->email;
            DB::table('users')->where('id', $user->id)->update([
                'role_id' => $roleIds[$roleCode],
                'username' => $username,
                'password_hash' => $user->password,
                'first_name' => $user->name,
                'last_name' => '',
            ]);

            if ($roleCode === 'STUDENT') {
                $facultyId = $user->faculty_id ?: DB::table('faculties')->value('id');
                $programId = $programIds[$facultyId] ?? DB::table('programs')->value('program_id');
                if ($programId) {
                    DB::table('students')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'student_code' => $user->student_number ?: 'STU-'.$user->id,
                            'program_id' => $programId,
                            'student_status' => 'STUDYING',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            } else {
                DB::table('staff')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'staff_code' => 'STAFF-'.$user->id,
                        'faculty_id' => $user->faculty_id,
                        'program_id' => $programIds[$user->faculty_id] ?? null,
                    ]
                );
            }
        }

        foreach (DB::table('companies')->get() as $company) {
            DB::table('companies')->where('id', $company->id)->update([
                'company_code' => 'LEGACY-'.(int) $company->id,
                'company_name_th' => $company->name,
                'postal_code' => $company->zipcode,
            ]);
        }
    }
}

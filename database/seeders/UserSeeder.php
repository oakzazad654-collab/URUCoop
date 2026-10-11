<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run()
    {
        $password = env('URU_COOP_DEMO_PASSWORD', 'password');

        $faculty = Faculty::firstOrCreate(['name' => 'คณะวิทยาการสารสนเทศ']);
        $department = Department::firstOrCreate([
            'faculty_id' => $faculty->id,
            'name' => 'สาขาเทคโนโลยีสารสนเทศ',
        ]);

        $advisor = User::updateOrCreate(
            ['email' => 'advisor@uru.ac.th'],
            [
                'name' => 'อาจารย์ที่ปรึกษาตัวอย่าง',
                'password' => Hash::make($password),
                'role' => 'advisor',
                'faculty_id' => $faculty->id,
                'department_id' => $department->id,
            ]
        );

        $student = User::updateOrCreate(
            ['email' => 'student@uru.ac.th'],
            [
                'name' => 'นักศึกษาตัวอย่าง',
                'password' => Hash::make($password),
                'role' => 'student',
                'student_number' => 'DEMO0001',
                'advisor_id' => $advisor->id,
                'faculty_id' => $faculty->id,
                'department_id' => $department->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'officer@uru.ac.th'],
            [
                'name' => 'เจ้าหน้าที่ศูนย์สหกิจศึกษาตัวอย่าง',
                'password' => Hash::make($password),
                'role' => 'officer',
                'faculty_id' => $faculty->id,
                'department_id' => $department->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@uru.ac.th'],
            [
                'name' => 'ผู้ดูแลระบบตัวอย่าง',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        if (!$student->advisor_id) {
            $student->update(['advisor_id' => $advisor->id]);
        }
    }
}
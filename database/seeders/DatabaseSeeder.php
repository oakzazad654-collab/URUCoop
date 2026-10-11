<?php

namespace Database\Seeders;

use App\Models\InternshipApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $password = env('URU_COOP_DEMO_PASSWORD', 'password');

        InternshipApplication::create([
            'company_name' => 'บริษัท ราชภัฏอุตรดิตถ์ จำกัด',
            'company_address' => '123 ถนนศรีสวัสดิ์ ตำบลท่าเกวียน อำเภอเมือง จังหวัดอุตรดิตถ์ 53000',
            'company_phone' => '055-123-456',
            'company_email' => 'hr@urucorp.com',
            'work_duration_months' => 6,
            'student_title_th' => 'นางสาว',
            'student_first_name_th' => 'พรพิมล',
            'student_last_name_th' => 'ทองคำ',
            'student_first_name_en' => 'Pornpimol',
            'student_last_name_en' => 'Thongkam',
            'student_id' => '64200123',
            'faculty' => 'คณะวิทยาการสารสนเทศ',
            'major' => 'สาขาเทคโนโลยีสารสนเทศ',
            'year_level' => 'ปีที่ 3',
            'gpa' => 3.65,
            'gpax' => 3.52,
            'birth_date' => '2003-05-12',
            'birth_place' => 'อุตรดิตถ์',
            'home_address' => '123 หมู่ 5 ตำบลคลองยาง อำเภอเมือง จังหวัดอุตรดิตถ์ 53000',
            'phone' => '081-234-5678',
            'email' => 'pornpimol.student@uru.ac.th',
            'height' => '162',
            'weight' => '48',
            'blood_type' => 'A',
            'religion' => 'พุทธ',
            'emergency_contact_name' => 'นายสมพงษ์ ทองคำ',
            'emergency_contact_relation' => 'บิดา',
            'emergency_contact_phone' => '081-111-2222',
            'father_name' => 'นายสมพงษ์ ทองคำ',
            'father_occupation' => 'ช่างซ่อมรถยนต์',
            'father_phone' => '081-111-2222',
            'mother_name' => 'นางสาวสมพร ทองคำ',
            'mother_occupation' => 'แม่บ้าน',
            'mother_phone' => '081-333-4444',
            'education_history' => [
                'มัธยมศึกษาตอนปลาย โรงเรียนสตรีอุตรดิตถ์',
                'ปริญญาตรี คณะวิทยาการสารสนเทศ มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            ],
            'language_skills' => [
                ['language' => 'ภาษาไทย', 'level' => 'ดีมาก'],
                ['language' => 'ภาษาอังกฤษ', 'level' => 'ดี'],
            ],
        ]);

        User::updateOrCreate(
            ['email' => 'admin@uru.ac.th'],
            [
                'name' => 'System Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'advisor@uru.ac.th'],
            [
                'name' => 'Advisor User',
                'password' => Hash::make($password),
                'role' => 'advisor',
            ]
        );

        User::updateOrCreate(
            ['email' => 'officer@uru.ac.th'],
            [
                'name' => 'Officer User',
                'password' => Hash::make($password),
                'role' => 'officer',
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@uru.ac.th'],
            [
                'name' => 'Student User',
                'password' => Hash::make($password),
                'role' => 'student',
            ]
        );

        $this->call([
            UserSeeder::class,
            CompanySeeder::class,
            CooperativeEducationSeeder::class,
            DataIsolationDemoSeeder::class,
            UrdruDemoSeeder::class,
        ]);
    }
}

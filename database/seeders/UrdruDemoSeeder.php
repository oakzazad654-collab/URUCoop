<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\AcademicTerm;
use App\Models\Company;
use App\Models\CoopApplication;
use App\Models\Placement;
use App\Models\Program;
use App\Models\ReportOutline;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UrdruDemoSeeder extends Seeder
{
    public function run()
    {
        $password = Hash::make(env('URU_COOP_DEMO_PASSWORD', '123456'));

        $roles = [
            'ADMIN' => ['label' => 'ผู้ดูแลระบบ', 'legacy' => 'admin'],
            'STUDENT' => ['label' => 'นักศึกษา', 'legacy' => 'student'],
            'COORDINATOR' => ['label' => 'อาจารย์ผู้ประสานงาน', 'legacy' => 'advisor'],
            'SUPERVISOR' => ['label' => 'อาจารย์นิเทศ', 'legacy' => 'supervisor'],
            'OFFICER' => ['label' => 'เจ้าหน้าที่สหกิจศึกษา', 'legacy' => 'officer'],
            'EXECUTIVE' => ['label' => 'ผู้บริหาร', 'legacy' => 'executive'],
            'COMPANY' => ['label' => 'สถานประกอบการ', 'legacy' => 'company'],
        ];

        foreach ($roles as $code => $details) {
            DB::table('roles')->updateOrInsert(
                ['role_code' => $code],
                ['role_name' => $details['label'], 'description' => null]
            );
        }

        $roleIds = DB::table('roles')->pluck('role_id', 'role_code');

        $faculties = [
            'SCI' => [
                'name' => 'คณะวิทยาการสารสนเทศ',
                'code' => 'SCI',
                'departments' => [
                    'สาขาวิทยาการสารสนเทศ',
                    'สาขาเทคโนโลยีสารสนเทศ',
                ],
                'programs' => [
                    ['code' => 'URU-INFO-IT', 'name' => 'เทคโนโลยีสารสนเทศ'],
                    ['code' => 'URU-INFO-CS', 'name' => 'วิทยาการคอมพิวเตอร์'],
                    ['code' => 'URU-INFO-IS', 'name' => 'ระบบสารสนเทศ'],
                ],
            ],
            'MGT' => [
                'name' => 'คณะวิทยาการจัดการ',
                'code' => 'MGT',
                'departments' => [
                    'สาขาการบัญชี',
                    'สาขาบริหารธุรกิจ',
                ],
                'programs' => [
                    ['code' => 'MGT-ACC', 'name' => 'การบัญชี'],
                    ['code' => 'MGT-MKT', 'name' => 'การตลาด'],
                    ['code' => 'MGT-BIZ', 'name' => 'ธุรกิจดิจิทัล'],
                ],
            ],
            'EDU' => [
                'name' => 'คณะครุศาสตร์',
                'code' => 'EDU',
                'departments' => [
                    'สาขาการศึกษาปฐมวัย',
                    'สาขาคณิตศาสตร์ศึกษา',
                ],
                'programs' => [
                    ['code' => 'EDU-ECE', 'name' => 'การศึกษาปฐมวัย'],
                    ['code' => 'EDU-MATH', 'name' => 'คณิตศาสตร์ศึกษา'],
                    ['code' => 'EDU-THAI', 'name' => 'ภาษาไทยศึกษา'],
                ],
            ],
        ];

        $facultyRows = [];
        foreach ($faculties as $shortCode => $details) {
            $faculty = Faculty::firstOrCreate(['name' => $details['name']]);
            $isCodeAvailable = !DB::table('faculties')->where('faculty_code', $shortCode)->where('id', '!=', $faculty->id)->exists();
            if ($isCodeAvailable) {
                DB::table('faculties')->where('id', $faculty->id)->update([
                    'faculty_code' => $shortCode,
                    'faculty_name' => $details['name'],
                    'status' => 'ACTIVE',
                ]);
            } else {
                DB::table('faculties')->where('id', $faculty->id)->update([
                    'faculty_name' => $details['name'],
                    'status' => 'ACTIVE',
                ]);
            }

            foreach ($details['departments'] as $departmentName) {
                Department::firstOrCreate([
                    'faculty_id' => $faculty->id,
                    'name' => $departmentName,
                ]);
            }

            $programIds = [];
            foreach ($details['programs'] as $program) {
                $model = Program::updateOrCreate(
                    ['program_code' => $program['code']],
                    [
                        'program_name' => $program['name'],
                        'faculty_id' => $faculty->id,
                        'status' => 'ACTIVE',
                    ]
                );
                $programIds[] = $model->program_id;
            }

            $facultyRows[$shortCode] = [
                'faculty_id' => $faculty->id,
                'department_id' => Department::where('faculty_id', $faculty->id)->value('id'),
                'programs' => $programIds,
            ];
        }

        $accounts = [
            'admin@uru.ac.th' => ['name' => 'ผู้ดูแลระบบ มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'role' => 'admin', 'role_code' => 'ADMIN', 'faculty' => null, 'position' => 'System Administrator'],
            'executive@uru.ac.th' => ['name' => 'ผู้บริหาร มหาวิทยาลัยราชภัฏอุตรดิตถ์', 'role' => 'executive', 'role_code' => 'EXECUTIVE', 'faculty' => null, 'position' => 'Executive'],
            'supervisor@uru.ac.th' => ['name' => 'อาจารย์นิเทศกลาง', 'role' => 'supervisor', 'role_code' => 'SUPERVISOR', 'faculty' => 'SCI', 'position' => 'Supervisor'],
        ];

        foreach ($accounts as $email => $details) {
            $facultyKey = $details['faculty'];
            $faculty = $facultyKey ? $faculties[$facultyKey] : null;
            $facultyId = $facultyKey ? $facultyRows[$facultyKey]['faculty_id'] : null;
            $departmentId = $facultyKey ? $facultyRows[$facultyKey]['department_id'] : null;

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $details['name'],
                    'password' => $password,
                    'role' => $details['role'],
                    'role_id' => $roleIds[$details['role_code']] ?? null,
                    'username' => str_replace('@', '_', $email),
                    'first_name' => $details['name'],
                    'last_name' => '',
                    'faculty_id' => $facultyId,
                    'department_id' => $departmentId,
                    'status' => 'ACTIVE',
                ]
            );

            if ($details['role'] === 'supervisor' || $details['role'] === 'advisor' || $details['role'] === 'officer') {
                $programId = $facultyKey ? $facultyRows[$facultyKey]['programs'][0] : null;
                Staff::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'staff_code' => strtoupper(substr($details['role'], 0, 3)).'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                        'faculty_id' => $facultyId,
                        'program_id' => $programId,
                        'position_name' => $details['position'],
                        'academic_position' => $details['position'],
                    ]
                );
            }
        }

        $nameList = [
            'กัญญารัตน์', 'นภัสสร', 'ศุภชัย', 'ภัทราวดี', 'วรัญญา', 'ธนภัทร', 'ปวริศา', 'พิชาภัทร', 'อธิษฐาน', 'ธนวัฒน์',
            'กนกพร', 'พรทิพย์', 'อริสา', 'สิริพร', 'พชร', 'มนัสวี', 'จิราพร', 'ณัฐวุฒิ', 'บุณยาพร', 'วรรณพร',
            'ทัศนีย์', 'ชลธิชา', 'ชานนท์', 'นริศรา', 'สุนิษา', 'ยศพัทธ์', 'เพชรลดา', 'ชาคริต', 'ณิชกานต์', 'ปริญญา'
        ];

        $lastNameList = [
            'ทองดี', 'ศรีสวัสดิ์', 'บุญมี', 'วัฒนกุล', 'พัฒนพงษ์', 'จันทร์สวัสดิ์', 'แสงสว่าง', 'กิตติธร', 'อ่อนละม้าย', 'ชูชัย',
            'พูนพงศ์', 'พิทักษ์', 'สิทธิพร', 'วรนุช', 'ชูศักดิ์', 'สิงห์ทอง', 'พรมทอง', 'ปัญญา', 'เจริญสุข', 'มณีรัตน์',
            'กาญจนาพร', 'รอดพงษ์', 'นวลละออง', 'คงสวัสดิ์', 'เพ็ชรสาคร', 'ประเสริฐ', 'สุขศรี', 'ลีลาพร', 'ทวีผล', 'สนิทใจ'
        ];

        $genderSequence = ['male', 'female'];
        $studentIndex = 1;

        foreach ($faculties as $shortCode => $details) {
            $facultyId = $facultyRows[$shortCode]['faculty_id'];
            $programs = $facultyRows[$shortCode]['programs'];
            $departmentId = $facultyRows[$shortCode]['department_id'];
            $advisorUser = User::updateOrCreate(
                ['email' => 'advisor.' . strtolower($shortCode) . '@uru.ac.th'],
                [
                    'name' => 'อาจารย์ผู้ประสานงาน ' . $shortCode,
                    'password' => $password,
                    'role' => 'advisor',
                    'role_id' => $roleIds['COORDINATOR'] ?? null,
                    'username' => 'advisor_' . strtolower($shortCode),
                    'first_name' => 'อาจารย์ผู้ประสานงาน',
                    'last_name' => $shortCode,
                    'faculty_id' => $facultyId,
                    'department_id' => $departmentId,
                    'status' => 'ACTIVE',
                ]
            );

            Staff::updateOrCreate(
                ['user_id' => $advisorUser->id],
                [
                    'staff_code' => 'ADV-' . $shortCode . '-' . str_pad((string) $advisorUser->id, 4, '0', STR_PAD_LEFT),
                    'faculty_id' => $facultyId,
                    'program_id' => $programs[0],
                    'position_name' => 'อาจารย์ผู้ประสานงาน',
                    'academic_position' => 'อาจารย์',
                ]
            );

            $officerUser = User::updateOrCreate(
                ['email' => 'officer.' . strtolower($shortCode) . '@uru.ac.th'],
                [
                    'name' => 'เจ้าหน้าที่สหกิจศึกษา ' . $shortCode,
                    'password' => $password,
                    'role' => 'officer',
                    'role_id' => $roleIds['OFFICER'] ?? null,
                    'username' => 'officer_' . strtolower($shortCode),
                    'first_name' => 'เจ้าหน้าที่สหกิจศึกษา',
                    'last_name' => $shortCode,
                    'faculty_id' => $facultyId,
                    'department_id' => $departmentId,
                    'status' => 'ACTIVE',
                ]
            );

            Staff::updateOrCreate(
                ['user_id' => $officerUser->id],
                [
                    'staff_code' => 'OFF-' . $shortCode . '-' . str_pad((string) $officerUser->id, 4, '0', STR_PAD_LEFT),
                    'faculty_id' => $facultyId,
                    'program_id' => $programs[0],
                    'position_name' => 'เจ้าหน้าที่สหกิจศึกษา',
                    'academic_position' => 'เจ้าหน้าที่',
                ]
            );

            for ($i = 1; $i <= 10; $i++) {
                $firstName = $nameList[(($studentIndex + $i) * 3) % count($nameList)];
                $lastName = $lastNameList[(($studentIndex + $i) * 5) % count($lastNameList)];
                $gender = $genderSequence[($i + $studentIndex) % 2];
                $studentNumber = sprintf('66%02d%04d', (int) $facultyId, $i);
                $email = 'student.' . strtolower($shortCode) . '.' . $i . '@uru.ac.th';
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $firstName . ' ' . $lastName,
                        'password' => $password,
                        'role' => 'student',
                        'role_id' => $roleIds['STUDENT'] ?? null,
                        'student_number' => $studentNumber,
                        'username' => $studentNumber,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'advisor_id' => $advisorUser->id,
                        'faculty_id' => $facultyId,
                        'department_id' => $departmentId,
                        'status' => 'ACTIVE',
                    ]
                );

                $programId = $programs[$i % count($programs)];
                Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_code' => $studentNumber,
                        'program_id' => $programId,
                        'title_name' => $gender === 'male' ? 'นาย' : 'นางสาว',
                        'gender' => strtoupper($gender),
                        'birth_date' => now()->subYears(20 + ($i % 3))->subDays($i * 7)->format('Y-m-d'),
                        'nationality' => 'ไทย',
                        'religion' => 'พุทธ',
                        'current_address' => 'ตำบลศรีประทาน อำเภอเมืองอุตรดิตถ์ จังหวัดอุตรดิตถ์',
                        'emergency_contact_name' => 'ผู้ปกครอง' . $lastName,
                        'emergency_contact_relation' => 'บิดา-มารดา',
                        'emergency_contact_phone' => sprintf('08%08d', (int) $facultyId * 1000 + $i),
                        'cumulative_gpa' => number_format(2.5 + (($i % 10) * 0.12), 2, '.', ''),
                        'student_status' => 'STUDYING',
                    ]
                );
            }

            $studentIndex += 10;
        }

        $this->seedWorkflowExamples($roleIds, $password);
    }

    private function seedWorkflowExamples($roleIds, $password)
    {
        $company = Company::updateOrCreate(
            ['company_code' => 'DEMO-URU-001'],
            [
                'name' => 'สถานประกอบการตัวอย่าง อุตรดิตถ์',
                'company_name_th' => 'สถานประกอบการตัวอย่าง อุตรดิตถ์',
                'company_name_en' => 'UDRU Cooperative Demo Company',
                'business_type' => 'เทคโนโลยีสารสนเทศ',
                'address' => 'ตำบลท่าอิฐ อำเภอเมืองอุตรดิตถ์ จังหวัดอุตรดิตถ์',
                'province' => 'อุตรดิตถ์',
                'postal_code' => '53000',
                'phone' => '055000000',
                'email' => 'company.demo@uru.ac.th',
                'contact_person' => 'ผู้ประสานงานตัวอย่าง',
                'status' => 'ACTIVE',
            ]
        );

        $companyUser = User::updateOrCreate(
            ['email' => 'company.demo@uru.ac.th'],
            [
                'name' => 'บัญชีสถานประกอบการตัวอย่าง',
                'password' => $password,
                'role' => 'company',
                'role_id' => $roleIds['COMPANY'] ?? null,
                'username' => 'company_demo',
                'first_name' => 'ผู้ประสานงานตัวอย่าง',
                'last_name' => '',
                'company_id' => $company->id,
                'status' => 'ACTIVE',
            ]
        );

        $contact = DB::table('company_contacts')->where([
            'company_id' => $company->id,
            'contact_type' => 'MENTOR',
            'email' => 'company.demo@uru.ac.th',
        ])->first();

        if (!$contact) {
            $contactId = DB::table('company_contacts')->insertGetId([
                'company_id' => $company->id,
                'contact_type' => 'MENTOR',
                'first_name' => 'ผู้ประสานงาน',
                'last_name' => 'ตัวอย่าง',
                'position_name' => 'พี่เลี้ยงนักศึกษา',
                'department_name' => 'เทคโนโลยีสารสนเทศ',
                'phone' => '055000000',
                'email' => 'company.demo@uru.ac.th',
                'is_primary' => true,
                'status' => 'ACTIVE',
            ]);
            $contact = DB::table('company_contacts')->where('contact_id', $contactId)->first();
        }

        $term = AcademicTerm::orderByDesc('academic_year')->orderByDesc('semester_no')->firstOrFail();
        $startDate = now()->addMonths(3)->toDateString();
        $endDate = now()->addMonths(6)->toDateString();
        $applicationStatuses = [
            'SCI' => ['DRAFT', 'PENDING'],
            'MGT' => ['APPROVED', 'QUALIFIED'],
            'EDU' => ['SUBMITTED', 'PENDING'],
        ];
        $applications = [];

        foreach ($applicationStatuses as $facultyCode => $statuses) {
            $studentUser = User::where('email', 'student.'.strtolower($facultyCode).'.1@uru.ac.th')->firstOrFail();
            $student = Student::where('user_id', $studentUser->id)->firstOrFail();
            $applicationNo = 'DEMO-'.$facultyCode.'-'.$term->academic_year;
            $application = CoopApplication::where('application_no', $applicationNo)->first();

            if (!$application) {
                $conflict = CoopApplication::where('student_id', $student->student_id)
                    ->where('term_id', $term->term_id)
                    ->exists();
                if ($conflict) {
                    if ($this->command) {
                        $this->command->warn('Skipped demo application for '.$studentUser->email.' because the student already has an application for this term.');
                    }
                    continue;
                }
            }

            $application = CoopApplication::updateOrCreate(
                ['application_no' => $applicationNo],
                [
                    'student_id' => $student->student_id,
                    'term_id' => $term->term_id,
                    'preferred_company_id' => $company->id,
                    'desired_position' => 'นักศึกษาฝึกปฏิบัติงานด้านเทคโนโลยีสารสนเทศ',
                    'expected_job_description' => 'พัฒนาระบบและสนับสนุนงานด้านเทคโนโลยีสารสนเทศ',
                    'work_start_date' => $startDate,
                    'work_end_date' => $endDate,
                    'application_date' => now()->toDateString(),
                    'qualification_result' => $statuses[1],
                    'application_status' => $statuses[0],
                    'student_note' => 'ข้อมูลตัวอย่างสำหรับทดสอบระบบเท่านั้น',
                    'submitted_at' => $statuses[0] === 'SUBMITTED' ? now() : null,
                ]
            );
            $applications[$facultyCode] = $application;
        }

        if (isset($applications['MGT'])) {
            $application = $applications['MGT'];
            $placement = Placement::updateOrCreate(
                ['application_id' => $application->application_id],
                [
                    'placement_no' => 'DEMO-PLC-'.$term->academic_year,
                    'student_id' => $application->student_id,
                    'company_id' => $company->id,
                    'term_id' => $term->term_id,
                    'coordinator_contact_id' => $contact->contact_id,
                    'mentor_contact_id' => $contact->contact_id,
                    'job_position' => 'นักศึกษาฝึกปฏิบัติงานด้านเทคโนโลยีสารสนเทศ',
                    'job_description' => 'ร่วมพัฒนาระบบและสนับสนุนการทำงานด้านเทคโนโลยีสารสนเทศ',
                    'department_name' => 'เทคโนโลยีสารสนเทศ',
                    'work_start_date' => $startDate,
                    'work_end_date' => $endDate,
                    'emergency_contact_name' => 'ผู้ประสานงานตัวอย่าง',
                    'emergency_contact_relation' => 'พี่เลี้ยง',
                    'emergency_contact_phone' => '055000000',
                    'emergency_contact_email' => 'company.demo@uru.ac.th',
                    'acceptance_status' => 'PENDING',
                    'placement_status' => 'PREPARING',
                ]
            );

            $workPlan = WorkPlan::updateOrCreate(
                ['placement_id' => $placement->placement_id],
                [
                    'plan_title' => 'แผนปฏิบัติงานสหกิจศึกษาตัวอย่าง',
                    'objective' => 'ฝึกประสบการณ์และประยุกต์ใช้ความรู้ด้านเทคโนโลยีสารสนเทศ',
                    'expected_result' => 'ส่งมอบงานและรายงานผลการปฏิบัติงานตามแผน',
                    'plan_status' => 'DRAFT',
                ]
            );
            DB::table('work_plan_items')->updateOrInsert(
                ['work_plan_id' => $workPlan->work_plan_id, 'sequence_no' => 1],
                [
                    'activity_name' => 'ศึกษาระบบและเครื่องมือของสถานประกอบการ',
                    'activity_description' => 'ทำความเข้าใจกระบวนการทำงานและเครื่องมือที่ใช้',
                    'start_date' => $startDate,
                    'end_date' => now()->addMonths(4)->toDateString(),
                    'expected_output' => 'สรุปความต้องการและแนวทางดำเนินงาน',
                    'weight_percent' => 25,
                ]
            );

            ReportOutline::updateOrCreate(
                ['placement_id' => $placement->placement_id],
                [
                    'report_title_th' => 'รายงานผลการปฏิบัติงานสหกิจศึกษาตัวอย่าง',
                    'report_title_en' => 'Cooperative Education Demo Report',
                    'report_type' => 'WORK_REPORT',
                    'background' => 'โครงร่างรายงานตัวอย่างสำหรับการทดสอบระบบ',
                    'objectives' => 'สรุปวัตถุประสงค์และผลการเรียนรู้จากการปฏิบัติงาน',
                    'scope_of_work' => 'งานด้านเทคโนโลยีสารสนเทศที่ได้รับมอบหมาย',
                    'methodology' => 'ศึกษาปัญหา วางแผน ดำเนินงาน และประเมินผล',
                    'expected_benefits' => 'ได้รายงานผลการปฏิบัติงานและข้อเสนอแนะ',
                    'outline_status' => 'DRAFT',
                ]
            );

            $studentUser = $application->student->user;
            $coordinator = $studentUser ? $studentUser->advisor : null;
            if ($coordinator) {
                $requestNo = 'DEMO-LTR-'.$term->academic_year;
                DB::table('letter_requests')->updateOrInsert(
                    ['request_no' => $requestNo],
                    [
                        'company_id' => $company->id,
                        'term_id' => $term->term_id,
                        'receiver_name' => 'ผู้จัดการฝ่ายทรัพยากรบุคคล',
                        'receiver_position' => 'ผู้จัดการ',
                        'request_date' => now()->toDateString(),
                        'work_start_date' => $startDate,
                        'work_end_date' => $endDate,
                        'coordinator_result' => 'PENDING',
                        'document_check_result' => 'PENDING',
                        'request_status' => 'SUBMITTED',
                        'created_by' => $application->student->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $requestId = DB::table('letter_requests')->where('request_no', $requestNo)->value('request_id');
                DB::table('letter_request_students')->updateOrInsert(
                    ['request_id' => $requestId, 'student_id' => $application->student_id],
                    ['application_id' => $application->application_id]
                );
                DB::table('approvals')->updateOrInsert(
                    [
                        'document_type' => 'LETTER_REQUEST',
                        'document_id' => $requestId,
                        'approval_step' => 1,
                        'approver_id' => $coordinator->id,
                    ],
                    [
                        'approver_role' => 'COORDINATOR',
                        'approval_status' => 'PENDING',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if ($this->command) {
            $this->command->info('Demo company account: '.$companyUser->email);
        }
    }
}

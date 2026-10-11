<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Announcement;
use App\Models\Company;
use App\Models\CoopApplication;
use App\Models\Faculty;
use App\Models\Placement;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_information_resources_gallery_and_board_are_available_to_guests()
    {
        $this->get(route('public.about'))->assertOk()->assertSee('สหกิจศึกษาคืออะไร');
        $this->get(route('public.forms'))->assertOk()
            ->assertSee('CWIE 01')->assertSee('สก. 15')->assertSee('/forms/CWIE-01.pdf')
            ->assertSee(route('public.forms.download', ['file' => 'สก15.pdf']));
        $this->get(route('public.selected-students'))->assertOk()->assertSee('นักศึกษาที่ผ่านการคัดเลือก');
        $this->get(route('public.activities'))->assertOk()->assertSee('ภาพกิจกรรม');
        $this->get(route('public.board'))->assertOk()->assertSee('กระดานข่าวประชาสัมพันธ์');
    }

    public function test_public_can_download_only_pdf_files_from_the_manuals_directory()
    {
        $response = $this->get(route('public.forms.download', ['file' => 'สก15.pdf']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            "filename*=utf-8''".rawurlencode('สก15.pdf'),
            $response->headers->get('content-disposition')
        );

        $this->get(route('public.forms.download', ['file' => 'สก.pdf']).'?inline=1')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('public.forms.download', ['file' => '..']))
            ->assertNotFound();
        $this->get(route('public.forms.download', ['file' => 'not-a-manual.pdf']))
            ->assertNotFound();
    }

    public function test_selected_student_directory_only_includes_approved_accepted_placements()
    {
        [$accepted, $studentUser] = $this->createPlacement('APPROVED', 'ACCEPTED');
        $this->createPlacement('SUBMITTED', 'PENDING');

        $this->get(route('public.selected-students'))
            ->assertOk()
            ->assertSee($studentUser->name)
            ->assertDontSee('DRAFT-Student');

        $this->assertSame($accepted->placement_id, Placement::where('acceptance_status', 'ACCEPTED')->first()->placement_id);
    }

    public function test_published_news_images_are_served_only_for_published_announcements()
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('activity.png');
        $path = $image->store('news', 'public');
        $published = Announcement::create([
            'title' => 'กิจกรรมเผยแพร่',
            'body' => 'รายละเอียดกิจกรรม',
            'image_path' => $path,
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ]);
        $draft = Announcement::create([
            'title' => 'ภาพฉบับร่าง',
            'body' => 'ยังไม่เผยแพร่',
            'image_path' => $path,
            'is_published' => false,
        ]);

        $this->get(route('public.news-image', $published))
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('public.news-image', $draft))->assertNotFound();
    }

    private function createPlacement($applicationStatus, $acceptanceStatus)
    {
        $faculty = Faculty::firstOrCreate(['name' => 'คณะรายชื่อ']);
        $program = Program::create([
            'program_code' => 'PUB-'.$applicationStatus.$acceptanceStatus,
            'program_name' => 'หลักสูตรรายชื่อ',
            'faculty_id' => $faculty->id,
            'status' => 'ACTIVE',
        ]);
        $studentUser = User::create([
            'name' => $applicationStatus === 'APPROVED' ? 'นักศึกษาผ่านคัดเลือก' : 'DRAFT-Student',
            'email' => strtolower($applicationStatus.$acceptanceStatus).'@example.test',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_code' => 'CODE-'.$studentUser->id,
            'program_id' => $program->program_id,
        ]);
        $company = Company::create(['name' => 'บริษัททดสอบ']);
        $term = AcademicTerm::firstOrCreate(
            ['semester_no' => 1, 'academic_year' => 2026],
            ['status' => 'OPEN']
        );
        $application = CoopApplication::create([
            'application_no' => 'APP-PUB-'.$student->student_id,
            'student_id' => $student->student_id,
            'term_id' => $term->term_id,
            'preferred_company_id' => $company->id,
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'application_date' => '2026-04-01',
            'application_status' => $applicationStatus,
        ]);
        $placement = Placement::create([
            'placement_no' => 'PLC-PUB-'.$student->student_id,
            'application_id' => $application->application_id,
            'student_id' => $student->student_id,
            'company_id' => $company->id,
            'term_id' => $term->term_id,
            'job_position' => 'นักศึกษาฝึกงาน',
            'job_description' => 'ฝึกงานและเรียนรู้',
            'work_start_date' => '2026-06-01',
            'work_end_date' => '2026-09-30',
            'acceptance_status' => $acceptanceStatus,
        ]);

        return [$placement, $studentUser];
    }
}

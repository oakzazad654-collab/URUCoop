<?php

namespace Tests\Feature;

use App\Models\CwieDocument;
use App\Models\User;
use App\Services\SkFormCatalog;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CwieWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cwie_document_moves_through_student_advisor_and_officer_approval()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->first();
        $advisor = User::where('email', 'advisor@uru.ac.th')->first();
        $officer = User::where('email', 'officer@uru.ac.th')->first();

        $document = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'แบบฟอร์ม CWIE 01 ทดสอบ',
            'user_id' => $student->id,
            'status' => 'draft',
            'student_id' => $student->student_number,
            'data' => ['test' => true],
        ]);

        $this->actingAs($student)
            ->post('/student/approvals/cwie-01/'.$document->id.'/submit')
            ->assertRedirect('/student/approvals');
        $this->assertDatabaseHas('cwie_documents', ['id' => $document->id, 'status' => 'pending_advisor']);

        $this->actingAs($advisor)
            ->post('/advisor/approvals/cwie-01/'.$document->id.'/approve', ['comment' => 'ผ่านการตรวจสอบ'])
            ->assertRedirect('/advisor/approvals');
        $this->assertDatabaseHas('cwie_documents', ['id' => $document->id, 'status' => 'pending_officer']);

        $this->actingAs($officer)
            ->post('/officer/approvals/cwie-01/'.$document->id.'/approve', ['comment' => 'อนุมัติ'])
            ->assertRedirect('/officer/approvals');
        $this->assertDatabaseHas('cwie_documents', ['id' => $document->id, 'status' => 'approved']);
        $this->assertDatabaseHas('approval_histories', ['document_id' => $document->id, 'action_type' => 'officer_approve']);
    }

    public function test_cwie_pdf_has_a_valid_signature_and_reasonable_page_count()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $document = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'แบบฟอร์ม CWIE 01 ทดสอบ PDF',
            'user_id' => $student->id,
            'status' => 'draft',
            'student_id' => $student->student_number,
            'data' => ['company_name' => 'สถานประกอบการทดสอบ', 'student_name' => $student->name],
        ]);

        $response = $this->actingAs($student)->get('/student/cwie/cwie-01/'.$document->id.'/pdf');
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', substr($pdf, -100));
        $pageTreeFound = preg_match('/\/Type\s*\/Pages\b.{0,160}?\/Count\s+(\d+)/s', $pdf, $matches);
        $this->assertSame(1, $pageTreeFound);
        $this->assertLessThanOrEqual(4, (int) $matches[1]);
    }

    public function test_cwie_forms_include_reference_downloads_and_official_student_fields()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();

        $this->actingAs($student)
            ->get('/student/cwie')
            ->assertOk()
            ->assertSee('แบบฟอร์ม สก.')
            ->assertSee('สก.15')
            ->assertSee('สก.12.1')
            ->assertDontSee('แบบฟอร์ม CWIE');

        $this->actingAs($student)
            ->get('/student/cwie/sk-01/create')
            ->assertOk()
            ->assertSee('กรอกข้อมูลลงในเอกสาร สก.01')
            ->assertSee('data[student_name]')
            ->assertSee('data[address_number]')
            ->assertSee('data[study_type]')
            ->assertSee('data[program_advisor_name]')
            ->assertSee('style="left:71.9%;top:17.6%;width:18%"', false)
            ->assertSee('style="left:69.2%;top:43.3%;width:21.7%"', false)
            ->assertSee('data-sk-pdf')
            ->assertSee('data-pdf-worker')
            ->assertSee('js/pdf.min.js')
            ->assertSee('js/sk-form-pdf.js')
            ->assertSee('<canvas', false)
            ->assertDontSee('<iframe', false)
            ->assertSee('forms/download/%E0%B8%AA%E0%B8%81.pdf?inline=1');
    }

    public function test_student_can_create_edit_preview_and_download_sk_forms()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();

        foreach (['sk-01', 'sk-08', 'sk-12.1', 'sk-15'] as $type) {
            $this->actingAs($student)->get('/student/cwie/'.$type.'/create')->assertOk();
        }

        $this->actingAs($student)
            ->post('/student/cwie/sk-01', ['data' => [
                'student_name' => $student->name,
                'student_id' => $student->student_number,
                'study_type' => 'ภาคปกติ',
                'address_number' => '12/3',
            ]])
            ->assertSessionHasNoErrors();

        $document = CwieDocument::where('user_id', $student->id)->where('type', 'sk-01')->latest()->firstOrFail();
        $this->assertDatabaseHas('cwie_documents', ['id' => $document->id, 'status' => 'draft', 'type' => 'sk-01']);
        $this->assertSame('12/3', data_get($document->data, 'address_number'));
        $this->assertSame('ภาคปกติ', data_get($document->data, 'study_type'));

        $this->actingAs($student)
            ->get('/student/cwie/sk-01/'.$document->id)
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('แบบฟอร์มต้นฉบับ')
            ->assertSee('กรอก/แก้ไขบนเอกสาร');

        $this->actingAs($student)
            ->get('/student/cwie/sk-01/'.$document->id.'/edit')
            ->assertOk()
            ->assertSee('value="'.$student->name.'"', false);

        $this->actingAs($student)
            ->put('/student/cwie/sk-01/'.$document->id, ['data' => [
                'student_name' => 'แก้ไขข้อมูลทดสอบ',
                'student_id' => $student->student_number,
                'study_type' => 'ภาคพิเศษ',
                'address_number' => '99/1',
            ]])
            ->assertRedirect('/student/cwie/sk-01/'.$document->id);

        $this->assertDatabaseHas('cwie_documents', ['id' => $document->id, 'data->student_name' => 'แก้ไขข้อมูลทดสอบ']);
        $this->assertSame('99/1', data_get($document->fresh()->data, 'address_number'));
        $this->assertSame('ภาคพิเศษ', data_get($document->fresh()->data, 'study_type'));

        $this->actingAs($student)
            ->get('/student/cwie/sk-01/'.$document->id.'/pdf?download=1')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="SK-01-'.$document->id.'.pdf"');

        $pdf = $this->actingAs($student)->get('/student/cwie/sk-01/'.$document->id.'/pdf')->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(200000, strlen($pdf));

        $this->actingAs($student)
            ->get('/forms/download/%E0%B8%AA%E0%B8%81.pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_calibrate_sk01_positions_and_student_form_uses_them()
    {
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $definition = app(SkFormCatalog::class)->all()['sk-01'];
        $positions = [];

        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                if (!empty($field['position'])) {
                    $positions[$field['key']] = [
                        'left' => $field['position']['left'],
                        'top' => $field['position']['top'],
                        'width' => $field['position']['width'],
                    ];
                }
            }
        }
        $positions['student_name']['left'] = 26.1;

        $this->actingAs($student)->get('/admin/sk01-positions')->assertForbidden();
        $this->actingAs($admin)
            ->get('/admin/sk01-positions')
            ->assertOk()
            ->assertSee('ลากช่องสีน้ำเงิน')
            ->assertSee('sk01-position-editor.js')
            ->assertSee('id="positionLeft" class="form-control" type="number" min="0" max="100" step="0.01"', false)
            ->assertSee('id="positionTop" class="form-control" type="number" min="0" max="100" step="0.01"', false)
            ->assertSee('id="positionWidth" class="form-control" type="number" min="0.1" max="100" step="0.01"', false);

        $this->actingAs($admin)
            ->put('/admin/sk01-positions', ['positions' => $positions])
            ->assertRedirect('/admin/sk01-positions');
        $this->assertDatabaseHas('website_settings', ['key' => 'sk_01_field_positions']);

        $this->actingAs($student)
            ->get('/student/cwie/sk-01/create')
            ->assertOk()
            ->assertSee('style="left:26.1%;top:17.6%;width:37.6%"', false);

        $this->actingAs($admin)
            ->delete('/admin/sk01-positions')
            ->assertRedirect('/admin/sk01-positions');
        $this->assertDatabaseMissing('website_settings', ['key' => 'sk_01_field_positions']);
    }

    public function test_admin_can_save_positions_for_other_sk_forms_independently()
    {
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $definition = app(SkFormCatalog::class)->all()['sk-02'];
        $positions = [];
        $index = 0;

        foreach ($definition['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $positions['field_'.count($positions)] = [
                    'left' => 7 + (($index % 4) * 23),
                    'top' => min(8 + (intdiv($index, 4) * 8), 96),
                    'width' => ($field['type'] ?? '') === 'radio' ? 4 : 19,
                ];
                $index++;
            }
        }
        foreach ($definition['repeaters'] as $repeaterIndex => $repeater) {
            foreach ($repeater['fields'] as $fieldIndex => $field) {
                $positions['repeater_'.$repeaterIndex.'_field_'.$fieldIndex] = [
                    'left' => 7 + (($index % 4) * 23),
                    'top' => min(8 + (intdiv($index, 4) * 8), 96),
                    'width' => ($field['type'] ?? '') === 'radio' ? 4 : 19,
                ];
                $index++;
            }
        }
        $positions['field_0']['left'] = 26.1;
        $positions['field_0']['page'] = 2;

        $this->actingAs($student)->get('/admin/sk-positions/sk-02')->assertForbidden();
        $this->actingAs($admin)
            ->get('/admin/sk-positions')
            ->assertOk()
            ->assertSee('SK.02')
            ->assertSee('SK.15')
            ->assertSee('12.1');
        $editorResponse = $this->actingAs($admin)
            ->get('/admin/sk-positions/sk-02')
            ->assertOk();
        $this->assertSame(
            count(array_merge(
                ...array_map(function ($section) { return $section['fields']; }, $definition['sections']),
                ...array_map(function ($repeater) { return $repeater['fields']; }, $definition['repeaters'])
            )),
            substr_count($editorResponse->getContent(), 'class="sk-position-field')
        );
        $editorResponse
            ->assertSee($definition['title'])
            ->assertSee('action="http://localhost/admin/sk-positions/sk-02"', false)
            ->assertSee('positions[repeater_0_field_0][left]', false)
            ->assertSee('positions[repeater_1_field_2][left]', false)
            ->assertSee('data-pdf-page')
            ->assertSee('id="autoPositionButton"', false)
            ->assertSee('data-label=', false);
        foreach (app(SkFormCatalog::class)->all() as $type => $formDefinition) {
            if ($type === 'sk-01') {
                continue;
            }

            $expectedPositionCount = 0;
            foreach ($formDefinition['sections'] as $section) {
                $expectedPositionCount += count($section['fields']);
            }
            foreach ($formDefinition['repeaters'] as $repeater) {
                $expectedPositionCount += count($repeater['fields']);
            }

            $formEditor = $this->actingAs($admin)->get('/admin/sk-positions/'.$type)->assertOk();
            $this->assertSame(
                $expectedPositionCount,
                substr_count($formEditor->getContent(), 'class="sk-position-field'),
                'Every defined field and repeating-table column should have an editor position for '.$type
            );
        }

        $this->actingAs($admin)
            ->put('/admin/sk-positions/sk-02', ['positions' => $positions])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/sk-positions/sk-02');
        $this->assertDatabaseHas('website_settings', ['key' => 'sk_02_field_positions']);
        $savedPositions = json_decode(DB::table('website_settings')->where('key', 'sk_02_field_positions')->value('value'), true);
        $this->assertSame(2, $savedPositions['field_0']['page']);
        $savedPositions['field_0']['page'] = null;
        DB::table('website_settings')
            ->where('key', 'sk_02_field_positions')
            ->update(['value' => json_encode($savedPositions)]);
        $this->actingAs($admin)
            ->get('/admin/sk-positions/sk-02')
            ->assertOk()
            ->assertSee('data-field="field_0"', false)
            ->assertSee('data-page="1"', false)
            ->assertSee('name="positions[field_0][page]" value="1"', false);

        $this->actingAs($admin)
            ->delete('/admin/sk-positions/sk-02')
            ->assertRedirect('/admin/sk-positions/sk-02');
        $this->assertDatabaseMissing('website_settings', ['key' => 'sk_02_field_positions']);
    }

    public function test_student_can_download_own_pdf_but_not_another_students_pdf()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $otherStudent = User::create([
            'name' => 'นักศึกษาอีกคน',
            'email' => 'another-student@uru.ac.th',
            'password' => Hash::make('test-password'),
            'role' => 'student',
            'student_number' => 'DEMO0002',
        ]);
        $document = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'แบบฟอร์ม CWIE 01 ดาวน์โหลดทดสอบ',
            'user_id' => $student->id,
            'status' => 'approved',
            'student_id' => $student->student_number,
            'data' => ['company_name' => 'สถานประกอบการทดสอบ', 'student_name' => $student->name],
        ]);

        $this->actingAs($student)
            ->get('/student/cwie/cwie-01/'.$document->id.'/pdf?download=1')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="cwie-01-'.$document->id.'.pdf"');

        $this->actingAs($otherStudent)
            ->get('/student/cwie/cwie-01/'.$document->id.'/pdf?download=1')
            ->assertForbidden();
    }

    public function test_cwie_01_photo_upload_is_saved_and_embedded_in_pdf()
    {
        Storage::fake('public');
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();

        $response = $this->actingAs($student)->post('/student/cwie/cwie-01', [
            'data' => [
                'company_name' => 'สถานประกอบการทดสอบ',
                'student_name' => $student->name,
                'student_id' => $student->student_number,
                'education' => [['level' => 'ปริญญาตรี']],
                'languages' => [['language' => 'อังกฤษ']],
            ],
            'student_photo' => UploadedFile::fake()->image('student-photo.png', 320, 400),
        ]);
        $response->assertRedirect();

        $document = CwieDocument::where('user_id', $student->id)->latest()->firstOrFail();
        $this->assertNotEmpty($document->student_photo_path);
        Storage::disk('public')->assertExists($document->student_photo_path);

        $pdf = $this->actingAs($student)
            ->get('/student/cwie/cwie-01/'.$document->id.'/pdf')
            ->assertOk()
            ->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('/Subtype /Image', $pdf);
    }
}

<?php

namespace Tests\Feature;

use App\Models\CwieDocument;
use App\Models\User;
use App\Notifications\DocumentApproved;
use App\Notifications\DocumentRevisionRequested;
use App\Notifications\DocumentSubmitted;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_document_moves_through_submission_and_approval_flow()
    {
        $this->seed(UserSeeder::class);

        Notification::fake();

        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $advisor = User::where('email', 'advisor@uru.ac.th')->firstOrFail();
        $officer = User::where('email', 'officer@uru.ac.th')->firstOrFail();

        $document = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'แบบฟอร์ม CWIE 01 ทดสอบ',
            'user_id' => $student->id,
            'status' => 'draft',
            'student_id' => $student->student_number,
            'data' => ['student_name' => $student->name],
        ]);

        $this->actingAs($student)
            ->from('/student/cwie')
            ->post('/student/approvals/cwie-01/' . $document->id . '/submit')
            ->assertRedirect('/student/approvals');

        $document->refresh();
        $this->assertSame('pending_advisor', $document->status);
        $this->assertNotNull($document->updated_at);
        $this->assertDatabaseHas('approval_histories', [
            'document_id' => $document->id,
            'document_type' => 'cwie-01',
            'action_type' => 'submitted',
        ]);
        Notification::assertSentTo($advisor, DocumentSubmitted::class);

        $this->actingAs($advisor)
            ->post('/advisor/approvals/cwie-01/' . $document->id . '/approve', ['comment' => 'ผ่านการตรวจสอบ'])
            ->assertRedirect('/advisor/approvals');

        $document->refresh();
        $this->assertSame('pending_officer', $document->status);
        $this->assertDatabaseHas('approval_histories', [
            'document_id' => $document->id,
            'action_type' => 'advisor_approve',
            'comment' => 'ผ่านการตรวจสอบ',
        ]);

        $this->actingAs($officer)
            ->post('/officer/approvals/cwie-01/' . $document->id . '/approve', ['comment' => 'อนุมัติเรียบร้อย'])
            ->assertRedirect('/officer/approvals');

        $document->refresh();
        $this->assertSame('approved', $document->status);
        $this->assertDatabaseHas('approval_histories', [
            'document_id' => $document->id,
            'action_type' => 'officer_approve',
            'comment' => 'อนุมัติเรียบร้อย',
        ]);
        Notification::assertSentTo($student, DocumentApproved::class);
    }

    public function test_revision_request_records_comment_and_notifies_student()
    {
        $this->seed(UserSeeder::class);

        Notification::fake();

        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $advisor = User::where('email', 'advisor@uru.ac.th')->firstOrFail();

        $document = CwieDocument::create([
            'type' => 'cwie-02',
            'title' => 'แบบฟอร์ม CWIE 02 ทดสอบ',
            'user_id' => $student->id,
            'status' => 'pending_advisor',
            'student_id' => $student->student_number,
            'data' => ['student_name' => $student->name],
        ]);

        $this->actingAs($advisor)
            ->post('/advisor/approvals/cwie-02/' . $document->id . '/revision', ['comment' => 'กรุณาแก้ไขประวัติการฝึกงาน'])
            ->assertRedirect('/advisor/approvals');

        $document->refresh();
        $this->assertSame('revision_requested', $document->status);
        $this->assertDatabaseHas('approval_histories', [
            'document_id' => $document->id,
            'action_type' => 'advisor_revision',
            'comment' => 'กรุณาแก้ไขประวัติการฝึกงาน',
        ]);
        Notification::assertSentTo($student, DocumentRevisionRequested::class);
    }

    public function test_document_workflow_events_push_formatted_line_notifications()
    {
        $this->seed(UserSeeder::class);
        config([
            'services.line.channel_access_token' => 'test-channel-access-token',
            'services.line.push_endpoint' => 'https://api.line.me/v2/bot/message/push',
        ]);
        Http::fake(['https://api.line.me/*' => Http::response([], 200)]);

        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $advisor = User::where('email', 'advisor@uru.ac.th')->firstOrFail();
        $studentLineId = 'U'.str_repeat('s', 32);
        $advisorLineId = 'U'.str_repeat('a', 32);
        $student->update(['line_user_id' => $studentLineId]);
        $advisor->update(['line_user_id' => $advisorLineId]);

        $submittedDocument = CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'แบบฟอร์ม CWIE 01 ส่งตรวจ',
            'user_id' => $student->id,
            'status' => 'draft',
            'student_id' => $student->student_number,
            'data' => ['student_name' => $student->name],
        ]);
        $this->actingAs($student)
            ->post('/student/approvals/cwie-01/'.$submittedDocument->id.'/submit')
            ->assertRedirect('/student/approvals');
        $this->assertSame(1, Http::recorded()->count());

        $revisionDocument = CwieDocument::create([
            'type' => 'cwie-02',
            'title' => 'แบบฟอร์ม CWIE 02 แก้ไข',
            'user_id' => $student->id,
            'status' => 'pending_advisor',
            'student_id' => $student->student_number,
            'data' => ['student_name' => $student->name],
        ]);
        $this->actingAs($advisor)
            ->post('/advisor/approvals/cwie-02/'.$revisionDocument->id.'/revision', ['comment' => 'กรุณาแก้ไขข้อมูล'])
            ->assertRedirect('/advisor/approvals');
        $this->assertSame(2, Http::recorded()->count());

        $approvedDocument = CwieDocument::create([
            'type' => 'cwie-03',
            'title' => 'แบบฟอร์ม CWIE 03 อนุมัติ',
            'user_id' => $student->id,
            'status' => 'pending_advisor',
            'student_id' => $student->student_number,
            'data' => ['student_name' => $student->name],
        ]);
        $this->actingAs($advisor)
            ->post('/advisor/approvals/cwie-03/'.$approvedDocument->id.'/approve', ['comment' => 'ผ่าน'])
            ->assertRedirect('/advisor/approvals');

        $this->assertSame(3, Http::recorded()->count());
        $requests = Http::recorded();
        $submittedPush = $requests->first(function ($pair) use ($advisorLineId) {
            return $pair[0]->data()['to'] === $advisorLineId;
        });
        $this->assertNotNull($submittedPush);
        $submittedPayload = $submittedPush[0]->data()['messages'][0];
        $submittedTexts = array_column($submittedPayload['contents']['body']['contents'], 'text');
        $this->assertContains('ชื่อผู้ยื่น: '.$student->name, $submittedTexts);
        $this->assertContains('รหัสเอกสาร: CWIE 01 #'.$submittedDocument->id, $submittedTexts);
        $this->assertContains('สถานะ: รออาจารย์ที่ปรึกษาตรวจสอบ', $submittedTexts);
        $this->assertSame(route('advisor.approvals.review', ['cwie-01', $submittedDocument->id]), $submittedPayload['contents']['footer']['contents'][0]['action']['uri']);

        $studentPushes = $requests->filter(function ($pair) use ($studentLineId) {
            return $pair[0]->data()['to'] === $studentLineId;
        })->values();
        $this->assertCount(2, $studentPushes);
        $revisionPayload = $studentPushes[0][0]->data()['messages'][0];
        $revisionTexts = array_column($revisionPayload['contents']['body']['contents'], 'text');
        $this->assertContains('สถานะ: รอแก้ไข', $revisionTexts);
        $this->assertContains('ความเห็น: กรุณาแก้ไขข้อมูล', $revisionTexts);

        $approvedPayload = $studentPushes[1][0]->data()['messages'][0];
        $approvedTexts = array_column($approvedPayload['contents']['body']['contents'], 'text');
        $this->assertContains('เอกสารได้รับการอนุมัติ', $approvedTexts);
        $this->assertContains('สถานะ: รอเจ้าหน้าที่ตรวจสอบ', $approvedTexts);
        $this->assertSame(route('student.approvals.review', ['cwie-03', $approvedDocument->id]), $approvedPayload['contents']['footer']['contents'][0]['action']['uri']);
    }

    public function test_line_test_command_sends_a_status_message_to_a_linked_user()
    {
        $this->seed(UserSeeder::class);
        config([
            'services.line.channel_token' => 'test-channel-access-token',
            'services.line.push_endpoint' => 'https://api.line.me/v2/bot/message/push',
        ]);
        Http::fake(['https://api.line.me/*' => Http::response([], 200)]);

        $user = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $lineUserId = 'U'.str_repeat('t', 32);
        $user->update(['line_user_id' => $lineUserId]);

        $this->artisan('test:line-notify', ['email' => $user->email])
            ->assertExitCode(0);

        Http::assertSent(function ($request) use ($lineUserId, $user) {
            return $request->data()['to'] === $lineUserId
                && strpos($request->data()['messages'][0]['text'], 'ทดสอบระบบแจ้งเตือน URU Coop-IS') !== false
                && strpos($request->data()['messages'][0]['text'], $user->name) !== false
                && $request->hasHeader('Authorization', 'Bearer test-channel-access-token');
        });
    }
}

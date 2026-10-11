<?php

namespace App\Notifications;

use App\Mail\CwieWorkflowMail;
use App\Models\CwieDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

abstract class CwieDocumentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $notificationType;
    public $documentType;
    public $documentId;
    public $documentTitle;
    public $studentName;
    public $studentNumber;
    public $status;
    public $comment;
    public $stage;
    public $createdAt;

    public function __construct(CwieDocument $document, $notificationType, $comment = null, $stage = null)
    {
        $this->notificationType = $notificationType;
        $this->documentType = $document->type;
        $this->documentId = $document->id;
        $this->documentTitle = $document->title;
        $this->studentName = data_get($document->data, 'student_name', $document->owner->name ?? '');
        $this->studentNumber = $document->student_id;
        $this->status = $document->status;
        $this->comment = $comment;
        $this->stage = $stage;
        $this->createdAt = now()->toIso8601String();
    }

    abstract protected function title();

    abstract protected function message();

    protected function urlFor($notifiable)
    {
        if ($this->documentType === 'cwie-06') {
            $route = $notifiable->role === 'officer' ? 'officer.approvals.review' : 'advisor.cwie06.show';
            $parameters = $notifiable->role === 'officer' ? [$this->documentType, $this->documentId] : [$this->documentId];
            return route($route, $parameters);
        }

        $routes = [
            'student' => 'student.approvals.review',
            'advisor' => 'advisor.approvals.review',
            'officer' => 'officer.approvals.review',
            'admin' => 'admin.approvals.review',
        ];
        return route($routes[$notifiable->role] ?? 'login', [$this->documentType, $this->documentId]);
    }

    protected function statusLabel()
    {
        return [
            'draft' => 'ฉบับร่าง',
            'pending_advisor' => 'รออาจารย์ที่ปรึกษาตรวจสอบ',
            'pending_officer' => 'รอเจ้าหน้าที่ตรวจสอบ',
            'revision_requested' => 'รอแก้ไข',
            'approved' => 'อนุมัติแล้ว',
            'rejected' => 'ไม่อนุมัติ',
        ][$this->status] ?? $this->status;
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        if ($notifiable->email) $channels[] = 'mail';
        if ($notifiable->line_user_id && config('services.line.channel_access_token')) $channels[] = \App\Notifications\Channels\LineChannel::class;
        return $channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'notification_type' => $this->notificationType,
            'title' => $this->title(),
            'message' => $this->message(),
            'document_type' => $this->documentType,
            'document_id' => $this->documentId,
            'document_title' => $this->documentTitle,
            'student_name' => $this->studentName,
            'student_id' => $this->studentNumber,
            'status' => $this->status,
            'comment' => $this->comment,
            'stage' => $this->stage,
            'url' => $this->urlFor($notifiable),
        ];
    }

    public function toMail($notifiable)
    {
        return (new CwieWorkflowMail([
            'title' => $this->title(),
            'message' => $this->message(),
            'document_title' => $this->documentTitle,
            'document_type' => strtoupper(str_replace('-', ' ', $this->documentType)),
            'student_name' => $this->studentName,
            'student_id' => $this->studentNumber,
            'status' => $this->status,
            'comment' => $this->comment,
            'url' => $this->urlFor($notifiable),
        ]))->to($notifiable->email);
    }

    public function toLine($notifiable)
    {
        $accent = $this->notificationType === 'document_approved' ? '#15803D' : ($this->notificationType === 'document_revision_requested' || $this->notificationType === 'document_rejected' ? '#B91C1C' : '#1D4ED8');
        $bodyContents = [
            ['type' => 'text', 'text' => $this->title(), 'weight' => 'bold', 'size' => 'md', 'wrap' => true, 'color' => $accent],
            ['type' => 'text', 'text' => $this->message(), 'size' => 'sm', 'wrap' => true, 'margin' => 'md', 'color' => '#334155'],
            ['type' => 'text', 'text' => 'ชื่อผู้ยื่น: '.$this->studentName, 'size' => 'xs', 'wrap' => true, 'margin' => 'md', 'color' => '#475569'],
            ['type' => 'text', 'text' => 'รหัสเอกสาร: '.strtoupper(str_replace('-', ' ', $this->documentType)).' #'.$this->documentId, 'size' => 'xs', 'wrap' => true, 'margin' => 'sm', 'color' => '#475569'],
            ['type' => 'text', 'text' => 'ชื่อเอกสาร: '.$this->documentTitle, 'size' => 'xs', 'wrap' => true, 'margin' => 'sm', 'color' => '#64748B'],
            ['type' => 'text', 'text' => 'สถานะ: '.$this->statusLabel(), 'size' => 'xs', 'wrap' => true, 'margin' => 'sm', 'color' => '#475569'],
        ];
        if ($this->comment) $bodyContents[] = ['type' => 'text', 'text' => 'ความเห็น: '.$this->comment, 'size' => 'xs', 'wrap' => true, 'margin' => 'md', 'color' => '#475569'];

        return [
            'type' => 'flex',
            'altText' => $this->title().' · '.$this->documentTitle,
            'contents' => [
                'type' => 'bubble',
                'size' => 'kilo',
                'body' => ['type' => 'box', 'layout' => 'vertical', 'spacing' => 'sm', 'contents' => $bodyContents],
                'footer' => ['type' => 'box', 'layout' => 'vertical', 'contents' => [
                    ['type' => 'button', 'style' => 'primary', 'color' => $accent, 'action' => ['type' => 'uri', 'label' => 'เปิดเอกสาร', 'uri' => $this->urlFor($notifiable)]],
                ]],
            ],
        ];
    }
}
<?php

namespace App\Services;

use App\Models\CwieDocument;
use App\Models\User;
use App\Notifications\DocumentApproved;
use App\Notifications\DocumentRejected;
use App\Notifications\DocumentRevisionRequested;
use App\Notifications\DocumentSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class CwieNotificationService
{
    public function documentSubmitted(CwieDocument $document)
    {
        $document->loadMissing('owner.advisor');
        if ($document->type === 'cwie-06') {
            $recipients = User::where('role', 'officer')->get();
        } else {
            $advisor = $document->owner ? $document->owner->advisor : null;
            $recipients = $advisor ? collect([$advisor]) : collect();
        }
        $this->send($recipients, new DocumentSubmitted($document, 'document_submitted'));
    }

    public function documentApproved(CwieDocument $document, $stage)
    {
        $recipient = $document->owner;
        if ($recipient) $this->send(collect([$recipient]), new DocumentApproved($document, 'document_approved', null, $stage));
    }

    public function documentRevisionRequested(CwieDocument $document, $comment, $stage)
    {
        $recipient = $document->owner;
        if ($recipient) $this->send(collect([$recipient]), new DocumentRevisionRequested($document, 'document_revision_requested', $comment, $stage));
    }

    public function documentRejected(CwieDocument $document, $comment, $stage)
    {
        $recipient = $document->owner;
        if ($recipient) $this->send(collect([$recipient]), new DocumentRejected($document, 'document_rejected', $comment, $stage));
    }

    private function send($recipients, $notification)
    {
        if ($recipients->isEmpty()) return;
        try {
            Notification::send($recipients, $notification);
        } catch (Throwable $exception) {
            Log::warning('CWIE notification dispatch failed.', [
                'notification' => get_class($notification),
                'recipient_count' => $recipients->count(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
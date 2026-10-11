<?php

namespace App\Notifications;

class DocumentApproved extends CwieDocumentNotification
{
    protected function title()
    {
        return 'เอกสารได้รับการอนุมัติ';
    }

    protected function message()
    {
        return $this->stage === 'advisor'
            ? 'อาจารย์ที่ปรึกษาอนุมัติแล้ว และส่งต่อให้เจ้าหน้าที่ศูนย์พิจารณาขั้นสุดท้าย'
            : 'เอกสารของคุณได้รับการอนุมัติขั้นสุดท้ายแล้ว';
    }
}
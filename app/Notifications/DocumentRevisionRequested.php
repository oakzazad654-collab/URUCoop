<?php

namespace App\Notifications;

class DocumentRevisionRequested extends CwieDocumentNotification
{
    protected function title()
    {
        return 'เอกสารถูกส่งกลับเพื่อแก้ไข';
    }

    protected function message()
    {
        return 'กรุณาตรวจสอบข้อเสนอแนะและแก้ไขเอกสารก่อนส่งพิจารณาอีกครั้ง';
    }
}
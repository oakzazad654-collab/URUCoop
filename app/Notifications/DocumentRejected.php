<?php

namespace App\Notifications;

class DocumentRejected extends CwieDocumentNotification
{
    protected function title()
    {
        return 'เอกสารไม่ได้รับการอนุมัติ';
    }

    protected function message()
    {
        return 'ผลการพิจารณาเอกสารของคุณ: ไม่อนุมัติ';
    }
}
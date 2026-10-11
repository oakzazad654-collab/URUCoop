<?php

namespace App\Notifications;

class DocumentSubmitted extends CwieDocumentNotification
{
    protected function title()
    {
        return 'มีเอกสาร CWIE รอพิจารณา';
    }

    protected function message()
    {
        return ($this->studentName ?: 'นักศึกษา').' ส่งเอกสารเพื่อพิจารณา';
    }
}
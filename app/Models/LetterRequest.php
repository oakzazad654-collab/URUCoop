<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterRequest extends Model
{
    protected $primaryKey = 'request_id';
    protected $fillable = [
        'request_no', 'company_id', 'term_id', 'receiver_name', 'receiver_position',
        'request_date', 'work_start_date', 'work_end_date', 'coordinator_result',
        'document_check_result', 'request_status', 'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'work_start_date' => 'date',
        'work_end_date' => 'date',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'letter_request_students', 'request_id', 'student_id')
            ->withPivot(['application_id', 'signed_at']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

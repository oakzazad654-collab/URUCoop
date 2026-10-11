<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoopApplication extends Model
{
    protected $primaryKey = 'application_id';
    protected $fillable = [
        'application_no', 'student_id', 'term_id', 'preferred_company_id',
        'desired_position', 'expected_job_description', 'work_start_date',
        'work_end_date', 'application_date', 'qualification_result',
        'application_status', 'student_note', 'submitted_at',
    ];

    protected $casts = [
        'work_start_date' => 'date',
        'work_end_date' => 'date',
        'application_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id', 'term_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'preferred_company_id');
    }

    public function reviews()
    {
        return $this->hasMany(QualificationReview::class, 'application_id', 'application_id');
    }

    public function placement()
    {
        return $this->hasOne(Placement::class, 'application_id', 'application_id');
    }
}

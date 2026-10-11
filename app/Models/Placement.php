<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    protected $primaryKey = 'placement_id';
    protected $fillable = [
        'placement_no', 'application_id', 'student_id', 'company_id', 'term_id',
        'coordinator_contact_id', 'mentor_contact_id', 'job_position',
        'job_description', 'department_name', 'work_start_date', 'work_end_date',
        'emergency_contact_name', 'emergency_contact_relation',
        'emergency_contact_address', 'emergency_contact_phone',
        'emergency_contact_email', 'acceptance_status', 'placement_status', 'confirmed_at',
    ];

    protected $casts = [
        'work_start_date' => 'date',
        'work_end_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(CoopApplication::class, 'application_id', 'application_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function mentorContact()
    {
        return $this->belongsTo(CompanyContact::class, 'mentor_contact_id', 'contact_id');
    }

    public function workPlan()
    {
        return $this->hasOne(WorkPlan::class, 'placement_id', 'placement_id');
    }

    public function reportOutline()
    {
        return $this->hasOne(ReportOutline::class, 'placement_id', 'placement_id');
    }
}

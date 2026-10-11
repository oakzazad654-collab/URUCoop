<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternshipApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'work_duration_months',
        'student_title_th',
        'student_first_name_th',
        'student_last_name_th',
        'student_first_name_en',
        'student_last_name_en',
        'student_id',
        'faculty',
        'major',
        'year_level',
        'gpa',
        'gpax',
        'birth_date',
        'birth_place',
        'home_address',
        'phone',
        'email',
        'height',
        'weight',
        'blood_type',
        'religion',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'father_name',
        'father_occupation',
        'father_phone',
        'mother_name',
        'mother_occupation',
        'mother_phone',
        'education_history',
        'language_skills',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'education_history' => 'array',
        'language_skills' => 'array',
    ];
}

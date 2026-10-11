<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';
    protected $hidden = [
        'citizen_id', 'birth_date', 'current_address', 'emergency_contact_name',
        'emergency_contact_relation', 'emergency_contact_phone',
    ];
    protected $fillable = [
        'user_id', 'student_code', 'program_id', 'citizen_id', 'title_name',
        'first_name_en', 'last_name_en', 'gender', 'birth_date', 'nationality',
        'religion', 'current_address', 'emergency_contact_name',
        'emergency_contact_relation', 'emergency_contact_phone',
        'cumulative_gpa', 'student_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'cumulative_gpa' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function applications()
    {
        return $this->hasMany(CoopApplication::class, 'student_id', 'student_id');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'student_id', 'student_id');
    }
}

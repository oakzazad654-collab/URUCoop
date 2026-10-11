<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $primaryKey = 'staff_id';
    protected $fillable = [
        'user_id', 'staff_code', 'faculty_id', 'program_id',
        'position_name', 'academic_position',
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}

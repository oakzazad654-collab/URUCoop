<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $primaryKey = 'program_id';
    protected $fillable = ['program_code', 'program_name', 'faculty_id', 'status'];
    public $timestamps = false;

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'program_id', 'program_id');
    }
}

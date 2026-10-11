<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['faculty_id', 'name'];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
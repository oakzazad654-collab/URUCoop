<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicTerm extends Model
{
    protected $primaryKey = 'term_id';
    protected $fillable = [
        'semester_no', 'academic_year', 'start_date', 'end_date',
        'registration_start_date', 'registration_end_date', 'status',
    ];
    public $timestamps = false;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
    ];

    public function applications()
    {
        return $this->hasMany(CoopApplication::class, 'term_id', 'term_id');
    }

    public function scopeOpenForRegistration($query, $date = null)
    {
        $date = $date ?: now()->toDateString();

        return $query->where('status', 'OPEN')
            ->where(function ($query) use ($date) {
                $query->whereNull('registration_start_date')
                    ->orWhereDate('registration_start_date', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('registration_end_date')
                    ->orWhereDate('registration_end_date', '>=', $date);
            });
    }
}

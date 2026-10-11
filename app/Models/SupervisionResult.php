<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisionResult extends Model
{
    protected $primaryKey = 'supervision_result_id';
    protected $fillable = [
        'schedule_id', 'supervisor_id', 'performance_summary', 'mentor_feedback',
        'student_feedback', 'problems_found', 'recommendations', 'result', 'visited_at',
    ];

    protected $casts = ['visited_at' => 'datetime'];

    public function schedule()
    {
        return $this->belongsTo(SupervisionSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}

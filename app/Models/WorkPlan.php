<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPlan extends Model
{
    protected $primaryKey = 'work_plan_id';
    protected $fillable = [
        'placement_id', 'plan_title', 'objective', 'expected_result',
        'student_confirmed_at', 'mentor_confirmed_at', 'plan_status',
        'reviewer_comment', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'student_confirmed_at' => 'datetime',
        'mentor_confirmed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class, 'placement_id', 'placement_id');
    }

    public function items()
    {
        return $this->hasMany(WorkPlanItem::class, 'work_plan_id', 'work_plan_id')->orderBy('sequence_no');
    }
}

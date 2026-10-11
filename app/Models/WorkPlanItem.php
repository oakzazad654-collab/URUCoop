<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPlanItem extends Model
{
    protected $primaryKey = 'work_plan_item_id';
    protected $fillable = [
        'work_plan_id', 'sequence_no', 'activity_name', 'activity_description',
        'start_date', 'end_date', 'expected_output', 'weight_percent',
    ];
    public $timestamps = false;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'weight_percent' => 'decimal:2',
    ];

    public function workPlan()
    {
        return $this->belongsTo(WorkPlan::class, 'work_plan_id', 'work_plan_id');
    }
}

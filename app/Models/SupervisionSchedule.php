<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisionSchedule extends Model
{
    protected $primaryKey = 'schedule_id';
    protected $fillable = [
        'supervision_request_id', 'company_id', 'placement_id', 'supervision_date',
        'start_time', 'end_time', 'appointment_detail', 'province', 'schedule_status',
    ];
    public $timestamps = false;

    protected $casts = ['supervision_date' => 'date'];

    public function request()
    {
        return $this->belongsTo(SupervisionRequest::class, 'supervision_request_id', 'supervision_request_id');
    }

    public function result()
    {
        return $this->hasOne(SupervisionResult::class, 'schedule_id', 'schedule_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function placement()
    {
        return $this->belongsTo(Placement::class, 'placement_id', 'placement_id');
    }
}

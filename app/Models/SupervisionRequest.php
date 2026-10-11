<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisionRequest extends Model
{
    protected $primaryKey = 'supervision_request_id';
    protected $fillable = [
        'request_no', 'term_id', 'requester_id', 'faculty_id',
        'request_date', 'purpose', 'request_status',
    ];

    protected $casts = ['request_date' => 'date'];

    public function schedules()
    {
        return $this->hasMany(SupervisionSchedule::class, 'supervision_request_id', 'supervision_request_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportOutline extends Model
{
    protected $primaryKey = 'outline_id';
    protected $fillable = [
        'placement_id', 'report_title_th', 'report_title_en', 'report_type',
        'background', 'objectives', 'scope_of_work', 'methodology',
        'expected_benefits', 'student_confirmed_at', 'mentor_confirmed_at',
        'outline_status', 'advisor_id', 'advisor_comment', 'reviewed_at',
    ];

    protected $casts = [
        'student_confirmed_at' => 'datetime',
        'mentor_confirmed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class, 'placement_id', 'placement_id');
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }
}

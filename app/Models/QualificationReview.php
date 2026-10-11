<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualificationReview extends Model
{
    protected $primaryKey = 'review_id';
    protected $fillable = [
        'application_id', 'reviewer_id', 'academic_result', 'behavior_result',
        'activity_result', 'health_result', 'result', 'comment', 'reviewed_at',
    ];
    public $timestamps = false;

    protected $casts = ['reviewed_at' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(CoopApplication::class, 'application_id', 'application_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}

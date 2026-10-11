<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CwieDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'user_id',
        'status',
        'student_id',
        'data',
        'student_photo_path',
        'map_image_path',
        'advisor_id',
        'officer_id',
        'advisor_approved_at',
        'advisor_rejected_at',
        'advisor_comment',
        'officer_approved_at',
        'officer_rejected_at',
        'officer_comment',
    ];

    protected $casts = [
        'data' => 'array',
        'advisor_approved_at' => 'datetime',
        'advisor_rejected_at' => 'datetime',
        'officer_approved_at' => 'datetime',
        'officer_rejected_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class, 'document_id')->where('document_type', $this->type)->orderBy('created_at');
    }
}
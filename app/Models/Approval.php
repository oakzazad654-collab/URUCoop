<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $primaryKey = 'approval_id';
    protected $fillable = [
        'document_type', 'document_id', 'approval_step', 'approver_id',
        'approver_role', 'approval_status', 'comment', 'action_at',
    ];

    protected $casts = ['action_at' => 'datetime'];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}

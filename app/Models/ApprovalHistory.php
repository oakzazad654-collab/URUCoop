<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalHistory extends Model
{
    protected $fillable = [
        'document_type',
        'document_id',
        'action_by',
        'action_type',
        'comment',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
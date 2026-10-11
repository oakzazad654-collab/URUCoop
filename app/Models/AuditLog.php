<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $primaryKey = 'audit_log_id';
    protected $fillable = [
        'user_id', 'action_name', 'table_name', 'record_id',
        'old_data', 'new_data', 'ip_address',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

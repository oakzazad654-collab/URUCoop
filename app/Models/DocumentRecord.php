<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRecord extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'document_id';
    protected $fillable = [
        'owner_user_id', 'related_type', 'related_id', 'document_name',
        'document_category', 'file_name', 'file_path', 'mime_type',
        'file_size', 'verification_status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $primaryKey = 'contact_id';
    protected $fillable = [
        'company_id', 'user_id', 'contact_type', 'title_name', 'first_name', 'last_name',
        'position_name', 'department_name', 'phone', 'email', 'is_primary', 'status',
    ];
    public $timestamps = false;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

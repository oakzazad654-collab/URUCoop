<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'student_number', 'advisor_id', 'faculty_id', 'department_id', 'company_id',
        'line_user_id', 'line_link_code_hash', 'line_link_expires_at', 'role_id',
        'username', 'phone', 'first_name', 'last_name', 'status', 'last_login_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'password_hash', 'remember_token', 'api_token_hash',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'line_link_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $user) {
            if ($user->isDirty('password')) {
                $user->password_hash = $user->password;
            }
        });
    }

    public function getAuthPassword()
    {
        return $this->password_hash ?: $this->password;
    }

    public function cwieDocuments()
    {
        return $this->hasMany(CwieDocument::class);
    }

    public function approvalHistories()
    {
        return $this->hasMany(ApprovalHistory::class, 'action_by');
    }

    public function advisor()
    {
        return $this->belongsTo(self::class, 'advisor_id');
    }

    public function advisees()
    {
        return $this->hasMany(self::class, 'advisor_id')->where('role', 'student');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function staffProfile()
    {
        return $this->hasOne(Staff::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companyContact()
    {
        return $this->hasOne(CompanyContact::class, 'user_id');
    }
}

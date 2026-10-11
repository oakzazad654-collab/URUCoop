<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'subdistrict',
        'district',
        'province',
        'zipcode',
        'google_maps_url',
        'company_code',
        'company_name_th',
        'company_name_en',
        'tax_id',
        'business_type',
        'address_no',
        'village_or_soi',
        'road',
        'postal_code',
        'fax',
        'website',
        'map_url',
        'travel_detail',
        'landmark',
        'created_by_id',
        'phone',
        'email',
        'contact_person',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function contacts()
    {
        return $this->hasMany(CompanyContact::class, 'company_id');
    }

    public function coopApplications()
    {
        return $this->hasMany(CoopApplication::class, 'preferred_company_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name' => 'บริษัท เทคโนโลยีไทย จำกัด',
            'address' => '123 ถ.สุขุมวิท กรุงเทพมหานคร',
            'phone' => '02-123-4567',
            'email' => 'info@techthai.co.th',
            'contact_person' => 'นางสาวกัญญารัตน์',
            'latitude' => 13.7563,
            'longitude' => 100.5018,
            'status' => 'active',
        ]);

        Company::create([
            'name' => 'บริษัท เอ็นจิเนียริ่ง แอนด์ ซัพพลาย จำกัด',
            'address' => '456 ถ.เชียงใหม่-ลำปาง เชียงใหม่',
            'phone' => '053-555-123',
            'email' => 'contact@engineering-supply.co',
            'contact_person' => 'นายสมชาย',
            'latitude' => 18.7883,
            'longitude' => 98.9853,
            'status' => 'pending',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CwieDocument;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyLocationMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_map_returns_geocoded_companies_and_approved_students()
    {
        $this->seed(UserSeeder::class);
        $advisor = User::where('email', 'advisor@uru.ac.th')->firstOrFail();
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();
        $company = Company::create([
            'name' => 'URU Map Test Company',
            'address' => '123 ถนนทดสอบ',
            'subdistrict' => 'ตำบลทดสอบ',
            'district' => 'อำเภอทดสอบ',
            'province' => 'อุตรดิตถ์',
            'zipcode' => '53000',
            'latitude' => 17.6200,
            'longitude' => 100.0993,
        ]);
        Company::create(['name' => 'Company without coordinates']);
        CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'CWIE 01 Map Test',
            'user_id' => $student->id,
            'status' => 'approved',
            'student_id' => $student->student_number,
            'data' => [
                'company_name' => ' uru map   test company ',
                'student_name' => $student->name,
                'major' => 'เทคโนโลยีสารสนเทศ',
            ],
        ]);
        CwieDocument::create([
            'type' => 'cwie-01',
            'title' => 'CWIE 01 Pending Map Test',
            'user_id' => $student->id,
            'status' => 'pending_advisor',
            'student_id' => $student->student_number,
            'data' => ['company_name' => $company->name, 'student_name' => $student->name],
        ]);

        $this->actingAs($advisor)->get('/staff/map')->assertOk()->assertSee('สถานที่ฝึกงาน');
        $response = $this->getJson('/api/companies/locations')->assertOk();

        $response->assertJsonCount(1);
        $response->assertJsonPath('0.id', $company->id);
        $response->assertJsonPath('0.student_count', 1);
        $response->assertJsonPath('0.students.0.student_id', $student->student_number);
        $response->assertJsonPath('0.students.0.major', 'เทคโนโลยีสารสนเทศ');
        $response->assertJsonPath('0.directions_url', 'https://www.google.com/maps/dir/?api=1&destination=17.62000000%2C100.09930000');
    }

    public function test_students_can_view_company_locations_but_not_the_staff_map()
    {
        $this->seed(UserSeeder::class);
        $student = User::where('email', 'student@uru.ac.th')->firstOrFail();

        $this->actingAs($student)->get('/staff/map')->assertForbidden();
        $this->actingAs($student)->get('/student/map')->assertOk();
        $this->actingAs($student)->getJson('/api/companies/locations')->assertOk();
    }

    public function test_admin_can_access_map_and_company_locations_api()
    {
        $this->seed(UserSeeder::class);
        $admin = User::where('email', 'admin@uru.ac.th')->firstOrFail();

        $this->actingAs($admin)->get('/admin/map')->assertOk()->assertSee('สถานที่ฝึกงาน');
        $this->actingAs($admin)->getJson('/api/companies/locations')->assertOk();
    }

    public function test_officer_can_save_company_address_without_coordinates()
    {
        $this->seed(UserSeeder::class);
        $officer = User::where('email', 'officer@uru.ac.th')->firstOrFail();

        $this->actingAs($officer)->post('/officer/companies', [
            'name' => 'Location Pending Company',
            'address' => '1 ถนนตัวอย่าง',
            'subdistrict' => 'ตำบลตัวอย่าง',
            'district' => 'อำเภอตัวอย่าง',
            'province' => 'อุตรดิตถ์',
            'zipcode' => '53000',
            'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=17.62,100.09',
            'status' => 'active',
        ])->assertRedirect('/officer/companies');

        $this->assertDatabaseHas('companies', [
            'name' => 'Location Pending Company',
            'subdistrict' => 'ตำบลตัวอย่าง',
            'district' => 'อำเภอตัวอย่าง',
            'province' => 'อุตรดิตถ์',
            'zipcode' => '53000',
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}

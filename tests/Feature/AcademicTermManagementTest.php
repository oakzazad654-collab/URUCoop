<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcademicTermManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_and_update_academic_terms()
    {
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'terms-admin@example.test',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin/academic-terms')
            ->assertOk()
            ->assertSee('ภาคการศึกษา');

        $this->actingAs($admin)->post('/admin/academic-terms', [
            'semester_no' => 1,
            'academic_year' => 2027,
            'start_date' => '2027-06-01',
            'end_date' => '2027-10-31',
            'registration_start_date' => '2027-01-01',
            'registration_end_date' => '2027-05-31',
            'status' => 'OPEN',
        ])->assertRedirect('/admin/academic-terms');

        $term = AcademicTerm::where('academic_year', 2027)->firstOrFail();
        $this->assertDatabaseHas('academic_terms', [
            'term_id' => $term->term_id,
            'semester_no' => 1,
            'status' => 'OPEN',
        ]);

        $this->actingAs($admin)->put('/admin/academic-terms/'.$term->term_id, [
            'semester_no' => 2,
            'academic_year' => 2027,
            'start_date' => '2027-11-01',
            'end_date' => '2028-03-31',
            'registration_start_date' => '2027-08-01',
            'registration_end_date' => '2027-10-31',
            'status' => 'PLANNED',
        ])->assertRedirect('/admin/academic-terms');

        $this->assertDatabaseHas('academic_terms', [
            'term_id' => $term->term_id,
            'semester_no' => 2,
            'status' => 'PLANNED',
        ]);
    }

    public function test_non_administrators_cannot_manage_academic_terms()
    {
        $officer = User::create([
            'name' => 'เจ้าหน้าที่',
            'email' => 'terms-officer@example.test',
            'password' => Hash::make('secret'),
            'role' => 'officer',
        ]);

        $this->actingAs($officer)
            ->get('/admin/academic-terms')
            ->assertForbidden();
    }

    public function test_administrator_cannot_create_duplicate_semester_and_year()
    {
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'terms-duplicate-admin@example.test',
            'password' => Hash::make('secret'),
            'role' => 'admin',
        ]);
        AcademicTerm::create([
            'semester_no' => 1,
            'academic_year' => 2027,
            'status' => 'PLANNED',
        ]);

        $this->actingAs($admin)
            ->post('/admin/academic-terms', [
                'semester_no' => 1,
                'academic_year' => 2027,
                'status' => 'OPEN',
            ])
            ->assertSessionHasErrors('semester_no');
    }
}

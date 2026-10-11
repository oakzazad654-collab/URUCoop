<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyInternshipApplicationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_legacy_application_actions()
    {
        $application = InternshipApplication::create([
            'company_name' => 'Private Company',
            'student_first_name_th' => 'Test',
            'student_last_name_th' => 'Student',
        ]);

        $this->get('/internship-applications')->assertRedirect('/login');
        $this->get('/internship-applications/create')->assertRedirect('/login');
        $this->get('/internship-applications/'.$application->id)->assertRedirect('/login');
        $this->get('/internship-applications/'.$application->id.'/edit')->assertRedirect('/login');
        $this->get('/internship-applications/'.$application->id.'/pdf')->assertRedirect('/login');
        $this->post('/internship-applications', [])->assertRedirect('/login');
        $this->put('/internship-applications/'.$application->id, [])->assertRedirect('/login');
        $this->delete('/internship-applications/'.$application->id)->assertRedirect('/login');
    }

    public function test_only_officers_and_admins_can_access_legacy_applications()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get('/internship-applications')->assertForbidden();

        $officer = User::factory()->create(['role' => 'officer']);
        $this->actingAs($officer)->get('/internship-applications')->assertOk();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/internship-applications')->assertOk();
    }

    public function test_faculty_officer_is_scoped_to_their_faculty_and_cannot_create_for_another_faculty()
    {
        $facultyA = Faculty::create(['name' => 'Faculty A']);
        $facultyB = Faculty::create(['name' => 'Faculty B']);
        InternshipApplication::create([
            'company_name' => 'Company A',
            'student_first_name_th' => 'Student',
            'student_last_name_th' => 'From A',
            'faculty' => 'Faculty A',
        ]);
        InternshipApplication::create([
            'company_name' => 'Company B',
            'student_first_name_th' => 'Student',
            'student_last_name_th' => 'From B',
            'faculty' => 'Faculty B',
        ]);
        $officer = User::factory()->create([
            'role' => 'officer',
            'faculty_id' => $facultyA->id,
        ]);

        $this->actingAs($officer)
            ->get('/internship-applications')
            ->assertOk()
            ->assertSee('From A')
            ->assertDontSee('From B');

        $this->actingAs($officer)
            ->post('/internship-applications', [
                'company_name' => 'Company A New',
                'student_first_name_th' => 'New',
                'student_last_name_th' => 'Student',
                'faculty' => 'Faculty B',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('internship_applications', [
            'company_name' => 'Company A New',
            'faculty' => 'Faculty A',
        ]);
    }
}

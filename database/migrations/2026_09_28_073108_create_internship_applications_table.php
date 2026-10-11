<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternshipApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->integer('work_duration_months')->nullable();

            $table->string('student_title_th')->nullable();
            $table->string('student_first_name_th');
            $table->string('student_last_name_th');
            $table->string('student_first_name_en')->nullable();
            $table->string('student_last_name_en')->nullable();
            $table->string('student_id')->nullable();
            $table->string('faculty')->nullable();
            $table->string('major')->nullable();
            $table->string('year_level')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->decimal('gpax', 4, 2)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->text('home_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('religion')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_phone')->nullable();

            $table->json('education_history')->nullable();
            $table->json('language_skills')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internship_applications');
    }
}

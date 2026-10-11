<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCooperativeEducationDomainTables extends Migration
{
    private $tables = [
        'audit_logs', 'documents', 'approvals', 'supervision_results',
        'supervision_schedules', 'supervision_members', 'supervision_requests',
        'report_outlines', 'work_plan_items', 'work_plans', 'placements',
        'letter_request_students', 'letter_requests', 'qualification_reviews',
        'coop_applications', 'company_contacts', 'staff', 'students',
        'academic_terms', 'programs', 'roles',
    ];

    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('role_id');
            $table->string('role_code', 50)->unique();
            $table->string('role_name', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->string('username', 100)->nullable()->unique();
            $table->string('api_token_hash', 64)->nullable()->unique();
            $table->string('password_hash', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->dateTime('last_login_at')->nullable();
            $table->foreign('role_id')->references('role_id')->on('roles')->nullOnDelete();
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->string('faculty_code', 30)->nullable()->unique();
            $table->string('faculty_name', 200)->nullable();
            $table->string('status', 20)->default('ACTIVE');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_code', 30)->nullable()->unique();
            $table->string('company_name_th', 255)->nullable();
            $table->string('company_name_en', 255)->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('business_type', 200)->nullable();
            $table->string('address_no', 50)->nullable();
            $table->string('village_or_soi', 100)->nullable();
            $table->string('road', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('map_url', 500)->nullable();
            $table->text('travel_detail')->nullable();
            $table->text('landmark')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->bigIncrements('program_id');
            $table->string('program_code', 30)->unique();
            $table->string('program_name', 200);
            $table->unsignedBigInteger('faculty_id');
            $table->string('status', 20)->default('ACTIVE');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });

        Schema::create('academic_terms', function (Blueprint $table) {
            $table->bigIncrements('term_id');
            $table->unsignedTinyInteger('semester_no');
            $table->unsignedSmallInteger('academic_year');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('registration_start_date')->nullable();
            $table->date('registration_end_date')->nullable();
            $table->string('status', 20)->default('PLANNED');
            $table->unique(['semester_no', 'academic_year']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('student_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('student_code', 30)->unique();
            $table->unsignedBigInteger('program_id');
            $table->string('citizen_id', 20)->nullable();
            $table->string('title_name', 30)->nullable();
            $table->string('first_name_en', 100)->nullable();
            $table->string('last_name_en', 100)->nullable();
            $table->string('gender', 20)->default('UNSPECIFIED');
            $table->date('birth_date')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('religion', 100)->nullable();
            $table->text('current_address')->nullable();
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->decimal('cumulative_gpa', 3, 2)->nullable();
            $table->string('student_status', 20)->default('STUDYING');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('program_id')->references('program_id')->on('programs');
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->bigIncrements('staff_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('staff_code', 30)->unique();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('position_name', 150)->nullable();
            $table->string('academic_position', 150)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('faculty_id')->references('id')->on('faculties')->nullOnDelete();
            $table->foreign('program_id')->references('program_id')->on('programs')->nullOnDelete();
        });

        Schema::create('company_contacts', function (Blueprint $table) {
            $table->bigIncrements('contact_id');
            $table->unsignedBigInteger('company_id');
            $table->string('contact_type', 30);
            $table->string('title_name', 30)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('position_name', 150)->nullable();
            $table->string('department_name', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('status', 20)->default('ACTIVE');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::create('coop_applications', function (Blueprint $table) {
            $table->bigIncrements('application_id');
            $table->string('application_no', 30)->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('preferred_company_id');
            $table->string('desired_position', 200)->nullable();
            $table->text('expected_job_description')->nullable();
            $table->date('work_start_date');
            $table->date('work_end_date');
            $table->date('application_date');
            $table->string('qualification_result', 30)->default('PENDING');
            $table->string('application_status', 30)->default('DRAFT');
            $table->text('student_note')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'term_id']);
            $table->foreign('student_id')->references('student_id')->on('students');
            $table->foreign('term_id')->references('term_id')->on('academic_terms');
            $table->foreign('preferred_company_id')->references('id')->on('companies');
        });

        Schema::create('qualification_reviews', function (Blueprint $table) {
            $table->bigIncrements('review_id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->boolean('academic_result')->default(false);
            $table->boolean('behavior_result')->default(false);
            $table->boolean('activity_result')->default(false);
            $table->boolean('health_result')->default(false);
            $table->string('result', 30);
            $table->text('comment')->nullable();
            $table->dateTime('reviewed_at');
            $table->foreign('application_id')->references('application_id')->on('coop_applications')->cascadeOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users');
        });

        Schema::create('letter_requests', function (Blueprint $table) {
            $table->bigIncrements('request_id');
            $table->string('request_no', 30)->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('term_id');
            $table->string('receiver_name', 200)->nullable();
            $table->string('receiver_position', 200)->nullable();
            $table->date('request_date');
            $table->date('work_start_date');
            $table->date('work_end_date');
            $table->string('coordinator_result', 30)->default('PENDING');
            $table->string('document_check_result', 20)->default('PENDING');
            $table->string('request_status', 30)->default('DRAFT');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('term_id')->references('term_id')->on('academic_terms');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('letter_request_students', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('application_id')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->primary(['request_id', 'student_id']);
            $table->foreign('request_id')->references('request_id')->on('letter_requests')->cascadeOnDelete();
            $table->foreign('student_id')->references('student_id')->on('students');
            $table->foreign('application_id')->references('application_id')->on('coop_applications')->nullOnDelete();
        });

        Schema::create('placements', function (Blueprint $table) {
            $table->bigIncrements('placement_id');
            $table->string('placement_no', 30)->unique();
            $table->unsignedBigInteger('application_id')->unique();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('coordinator_contact_id')->nullable();
            $table->unsignedBigInteger('mentor_contact_id')->nullable();
            $table->string('job_position', 200);
            $table->text('job_description');
            $table->string('department_name', 200)->nullable();
            $table->date('work_start_date');
            $table->date('work_end_date');
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();
            $table->text('emergency_contact_address')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_email', 150)->nullable();
            $table->string('acceptance_status', 20)->default('PENDING');
            $table->string('placement_status', 20)->default('PREPARING');
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
            $table->foreign('application_id')->references('application_id')->on('coop_applications');
            $table->foreign('student_id')->references('student_id')->on('students');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('term_id')->references('term_id')->on('academic_terms');
            $table->foreign('coordinator_contact_id')->references('contact_id')->on('company_contacts')->nullOnDelete();
            $table->foreign('mentor_contact_id')->references('contact_id')->on('company_contacts')->nullOnDelete();
        });

        Schema::create('work_plans', function (Blueprint $table) {
            $table->bigIncrements('work_plan_id');
            $table->unsignedBigInteger('placement_id')->unique();
            $table->string('plan_title');
            $table->text('objective')->nullable();
            $table->text('expected_result')->nullable();
            $table->dateTime('student_confirmed_at')->nullable();
            $table->dateTime('mentor_confirmed_at')->nullable();
            $table->string('plan_status', 30)->default('DRAFT');
            $table->text('reviewer_comment')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->foreign('placement_id')->references('placement_id')->on('placements')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('work_plan_items', function (Blueprint $table) {
            $table->bigIncrements('work_plan_item_id');
            $table->unsignedBigInteger('work_plan_id');
            $table->unsignedInteger('sequence_no');
            $table->string('activity_name');
            $table->text('activity_description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('expected_output')->nullable();
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->unique(['work_plan_id', 'sequence_no']);
            $table->foreign('work_plan_id')->references('work_plan_id')->on('work_plans')->cascadeOnDelete();
        });

        Schema::create('report_outlines', function (Blueprint $table) {
            $table->bigIncrements('outline_id');
            $table->unsignedBigInteger('placement_id')->unique();
            $table->string('report_title_th');
            $table->string('report_title_en')->nullable();
            $table->string('report_type', 30)->default('WORK_REPORT');
            $table->text('background')->nullable();
            $table->text('objectives')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('methodology')->nullable();
            $table->text('expected_benefits')->nullable();
            $table->dateTime('student_confirmed_at')->nullable();
            $table->dateTime('mentor_confirmed_at')->nullable();
            $table->string('outline_status', 30)->default('DRAFT');
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->text('advisor_comment')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('placement_id')->references('placement_id')->on('placements')->cascadeOnDelete();
            $table->foreign('advisor_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('supervision_requests', function (Blueprint $table) {
            $table->bigIncrements('supervision_request_id');
            $table->string('request_no', 30)->unique();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('faculty_id');
            $table->date('request_date');
            $table->text('purpose')->nullable();
            $table->string('request_status', 30)->default('DRAFT');
            $table->timestamps();
            $table->foreign('term_id')->references('term_id')->on('academic_terms');
            $table->foreign('requester_id')->references('id')->on('users');
            $table->foreign('faculty_id')->references('id')->on('faculties');
        });

        Schema::create('supervision_members', function (Blueprint $table) {
            $table->bigIncrements('supervision_member_id');
            $table->unsignedBigInteger('supervision_request_id');
            $table->unsignedBigInteger('staff_id');
            $table->string('member_role', 20)->default('MEMBER');
            $table->unique(['supervision_request_id', 'staff_id']);
            $table->foreign('supervision_request_id')->references('supervision_request_id')->on('supervision_requests')->cascadeOnDelete();
            $table->foreign('staff_id')->references('staff_id')->on('staff');
        });

        Schema::create('supervision_schedules', function (Blueprint $table) {
            $table->bigIncrements('schedule_id');
            $table->unsignedBigInteger('supervision_request_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('placement_id')->nullable();
            $table->date('supervision_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('appointment_detail')->nullable();
            $table->string('province', 100)->nullable();
            $table->string('schedule_status', 20)->default('PLANNED');
            $table->foreign('supervision_request_id')->references('supervision_request_id')->on('supervision_requests')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('placement_id')->references('placement_id')->on('placements')->nullOnDelete();
        });

        Schema::create('supervision_results', function (Blueprint $table) {
            $table->bigIncrements('supervision_result_id');
            $table->unsignedBigInteger('schedule_id')->unique();
            $table->unsignedBigInteger('supervisor_id');
            $table->text('performance_summary')->nullable();
            $table->text('mentor_feedback')->nullable();
            $table->text('student_feedback')->nullable();
            $table->text('problems_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('result', 30)->nullable();
            $table->dateTime('visited_at');
            $table->timestamps();
            $table->foreign('schedule_id')->references('schedule_id')->on('supervision_schedules')->cascadeOnDelete();
            $table->foreign('supervisor_id')->references('id')->on('users');
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->bigIncrements('approval_id');
            $table->string('document_type', 30);
            $table->unsignedBigInteger('document_id');
            $table->unsignedInteger('approval_step');
            $table->unsignedBigInteger('approver_id');
            $table->string('approver_role', 100)->nullable();
            $table->string('approval_status', 30)->default('PENDING');
            $table->text('comment')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_id']);
            $table->index(['approver_id', 'approval_status']);
            $table->foreign('approver_id')->references('id')->on('users');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('document_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->string('related_type', 30);
            $table->unsignedBigInteger('related_id');
            $table->string('document_name');
            $table->string('document_category', 100)->nullable();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('verification_status', 20)->default('PENDING');
            $table->timestamps();
            $table->index(['related_type', 'related_id']);
            $table->foreign('owner_user_id')->references('id')->on('users');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('audit_log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action_name', 100);
            $table->string('table_name', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['table_name', 'record_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropUnique(['company_code']);
            $table->dropColumn([
                'company_code', 'company_name_th', 'company_name_en', 'tax_id',
                'business_type', 'address_no', 'village_or_soi', 'road',
                'postal_code', 'fax', 'website', 'map_url', 'travel_detail',
                'landmark', 'created_by_id',
            ]);
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropUnique(['faculty_code']);
            $table->dropColumn(['faculty_code', 'faculty_name', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropUnique(['username']);
            $table->dropUnique(['api_token_hash']);
            $table->dropColumn(['role_id', 'username', 'api_token_hash', 'password_hash', 'phone', 'first_name', 'last_name', 'status', 'last_login_at']);
        });

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }
}
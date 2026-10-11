<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacultiesDepartmentsAndUserAssignments extends Migration
{
    public function up()
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['faculty_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number', 50)->nullable()->unique();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['advisor_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['department_id']);
            $table->dropUnique(['student_number']);
            $table->dropColumn(['student_number', 'advisor_id', 'faculty_id', 'department_id']);
        });

        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
    }
}
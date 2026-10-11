<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMentorAccountsAndRegistrationStatus extends Migration
{
    public function up()
    {
        Schema::table('company_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('company_contacts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}

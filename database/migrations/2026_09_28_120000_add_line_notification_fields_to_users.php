<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLineNotificationFieldsToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('line_user_id', 64)->nullable()->unique();
            $table->string('line_link_code_hash', 64)->nullable()->index();
            $table->timestamp('line_link_expires_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['line_user_id']);
            $table->dropIndex(['line_link_code_hash']);
            $table->dropColumn(['line_user_id', 'line_link_code_hash', 'line_link_expires_at']);
        });
    }
}
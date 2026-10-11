<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalWorkflowFields extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student')->index();
        });

        Schema::table('cwie_documents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('advisor_approved_at')->nullable();
            $table->timestamp('advisor_rejected_at')->nullable();
            $table->text('advisor_comment')->nullable();
            $table->timestamp('officer_approved_at')->nullable();
            $table->timestamp('officer_rejected_at')->nullable();
            $table->text('officer_comment')->nullable();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('cwie_documents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['advisor_id']);
            $table->dropForeign(['officer_id']);
            $table->dropColumn([
                'user_id', 'status', 'advisor_approved_at', 'advisor_rejected_at', 'advisor_comment',
                'officer_approved_at', 'officer_rejected_at', 'officer_comment', 'advisor_id', 'officer_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
}
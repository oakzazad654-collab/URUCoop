<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 10);
            $table->unsignedBigInteger('document_id');
            $table->foreignId('action_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 40);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['document_type', 'document_id', 'created_at'], 'approval_histories_document_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_histories');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCwieDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('cwie_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('title');
            $table->string('student_id', 50)->nullable()->index();
            $table->json('data');
            $table->string('student_photo_path')->nullable();
            $table->string('map_image_path')->nullable();
            $table->timestamps();
            $table->index(['type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cwie_documents');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->string('excerpt', 1000)->nullable();
            $table->longText('body');
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->dateTime('published_at')->nullable()->index();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
}
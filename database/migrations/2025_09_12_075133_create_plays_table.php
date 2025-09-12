<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaysTable extends Migration
{
    public function up()
    {
        Schema::create('plays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('song_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('device')->nullable();
            $table->timestamps();

            $table->foreign('song_id')->references('id')->on('songs')->onDelete('cascade');
            // user_id optional -> keep it no FK if you prefer, or reference users:
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plays');
    }
}


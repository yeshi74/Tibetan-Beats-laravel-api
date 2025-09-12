<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArtistidAndStatusToSongs extends Migration
{
    public function up()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->unsignedBigInteger('artist_id')->nullable()->after('id');
            // status: published|pending|blocked
            $table->string('status')->default('published')->after('cover_image');
            $table->foreign('artist_id')->references('id')->on('artists')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
            $table->dropColumn(['artist_id', 'status']);
        });
    }
}


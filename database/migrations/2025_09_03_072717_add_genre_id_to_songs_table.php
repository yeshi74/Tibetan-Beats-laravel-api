<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('songs', function (Blueprint $table) {
        $table->foreignId('genre_id')->nullable()->constrained()->onDelete('set null');
        $table->dropColumn('genre'); // remove old text column if exists
    });
}

public function down()
{
    Schema::table('songs', function (Blueprint $table) {
        $table->string('genre')->nullable();
        $table->dropConstrainedForeignId('genre_id');
    });
}

};

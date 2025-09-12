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
    Schema::table('dedications', function (Blueprint $table) {
        // Drop existing FK if it exists
        $table->dropForeign(['song_id']); 

        // Make sure column is nullable
        $table->unsignedBigInteger('song_id')->nullable()->change();

        // Re-add FK with onDelete('set null')
        $table->foreign('song_id')
              ->references('id')->on('songs')
              ->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dedications', function (Blueprint $table) {
            //
        });
    }
};

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
        Schema::create('dedication_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dedication_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['dedication_id', 'user_id']); // prevent double likes
        });
    }

    public function down()
    {
        Schema::dropIfExists('dedication_likes');
    }

};

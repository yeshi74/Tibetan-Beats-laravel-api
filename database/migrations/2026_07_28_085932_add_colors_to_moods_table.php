<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->string('start_color', 7)->default('#8E24AA')->after('emoji');
            $table->string('end_color', 7)->default('#AB47BC')->after('start_color');
        });
    }

    public function down(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->dropColumn([
                'start_color',
                'end_color',
            ]);
        });
    }
};

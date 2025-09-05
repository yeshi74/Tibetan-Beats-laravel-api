<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('in_app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->nullable(); // optional slug
            $table->string('type')->default('modal'); // modal | banner | card | bottom_sheet | interstitial
            $table->json('title')->nullable(); // localized JSON (e.g. {"en":"Hi"})
            $table->json('body')->nullable();
            $table->string('image_url')->nullable();
            $table->json('cta')->nullable(); // { "type":"route"|"url", "value":"/route", "label":{ "en":"Open" } }
            $table->string('audience')->default('all'); // all | role:xxx | users:[1,2] | country:IN | premium
            $table->integer('priority')->default(50);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('max_impressions_per_user')->nullable();
            $table->integer('max_impressions_global')->nullable();
            $table->boolean('dismissible')->default(true);
            $table->boolean('dismiss_persists')->default(true);
            $table->string('status')->default('active'); // draft | active | archived
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedInteger('impressions_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('in_app_messages');
    }
};

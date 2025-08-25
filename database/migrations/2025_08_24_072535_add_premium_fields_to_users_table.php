<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Premium flags
            $table->timestamp('premium_ends_at')->nullable()->after('is_premium');

            // Stripe identifiers (nullable until created)
            $table->string('stripe_customer_id')->nullable()->after('premium_ends_at');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');

            // Optional: extras for your UI
            // $table->string('avatar_url')->nullable()->after('name');
            // $table->string('country')->nullable()->after('avatar_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_premium',
                'premium_ends_at',
                'stripe_customer_id',
                'stripe_subscription_id',
                // 'avatar_url',
                // 'country',
            ]);
        });
    }
};

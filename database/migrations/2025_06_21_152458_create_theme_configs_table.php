<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('theme_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name_ecosystem')->nullable();
            $table->string('name_fiscal')->nullable();
            $table->string('direction')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state')->nullable();
            $table->string('email')->nullable();
            $table->text('legal_advise')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('cookies_policy')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->integer('active_coin_free')->default(1);
            $table->integer('active_coin_premium')->default(1);
            $table->integer('active_polls')->default(1);
            $table->integer('active_seasons')->default(1);
            $table->integer('active_items')->default(1);
            $table->integer('active_coupons')->default(1);
            $table->integer('active_store')->default(1);
            $table->integer('active_referrals')->default(1);
            $table->integer('active_hedera')->default(0);
            $table->integer('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_configs');
    }
};

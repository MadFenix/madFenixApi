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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('color_primary')->nullable();
            $table->string('color_secondary')->nullable();
            $table->string('color_white0')->nullable();
            $table->string('color_black')->nullable();
            $table->string('color_background_secondary')->nullable();
            $table->string('title_profile')->nullable();
            $table->string('title_avatar')->nullable();
            $table->string('title_state')->nullable();
            $table->string('title_coin_premium')->nullable();
            $table->string('title_coin_free')->nullable();
            $table->string('title_polls')->nullable();
            $table->string('title_items')->nullable();
            $table->string('title_collections')->nullable();
            $table->string('title_season')->nullable();
            $table->string('title_season_level')->nullable();
            $table->string('title_season_points')->nullable();
            $table->string('title_season_premium')->nullable();
            $table->string('title_store')->nullable();
            $table->string('title_store_h1')->nullable();
            $table->integer('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};

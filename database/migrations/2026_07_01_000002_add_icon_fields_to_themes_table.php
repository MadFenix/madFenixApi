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
        Schema::table('themes', function (Blueprint $table) {
            $table->string('icon_polls')->nullable()->after('title_store_h1');
            $table->string('icon_items')->nullable()->after('icon_polls');
            $table->string('icon_coin_free')->nullable()->after('icon_items');
            $table->string('icon_coin_premium')->nullable()->after('icon_coin_free');
            $table->string('icon_rewards_pass')->nullable()->after('icon_coin_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn([
                'icon_polls',
                'icon_items',
                'icon_coin_free',
                'icon_coin_premium',
                'icon_rewards_pass'
            ]);
        });
    }
};

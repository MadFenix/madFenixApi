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
        Schema::table('theme_configs', function (Blueprint $table) {
            $table->string('slogan_ecosystem')->nullable()->after('name_ecosystem');
            $table->string('url_instagram')->nullable()->after('privacy_policy');
            $table->string('url_youtube')->nullable()->after('url_instagram');
            $table->string('url_twitch')->nullable()->after('url_youtube');
            $table->string('url_x')->nullable()->after('url_twitch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_configs', function (Blueprint $table) {
            $table->dropColumn('slogan_ecosystem');
            $table->dropColumn('url_instagram');
            $table->dropColumn('url_youtube');
            $table->dropColumn('url_twitch');
            $table->dropColumn('url_x');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update3ProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('twitch_user_id')->nullable()->after('season_premium');
            $table->string('twitch_user_name')->nullable()->after('twitch_user_id');
            $table->string('twitch_api_user_token')->nullable()->after('twitch_user_name');
            $table->string('twitch_api_user_refresh_token')->nullable()->after('twitch_api_user_token');
            $table->string('twitch_scope')->nullable()->after('twitch_api_user_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['twitch_user_id','twitch_api_user_token','twitch_api_user_refresh_token','twitch_scope']);
        });
    }
}

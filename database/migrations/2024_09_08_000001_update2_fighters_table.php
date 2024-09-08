<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update2FightersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fighter_users', function (Blueprint $table) {
            $table->string('playing_card_left_back')->nullable();
            $table->string('playing_card_center_back')->nullable();
            $table->string('playing_card_right_back')->nullable();
        });
        Schema::table('fighter_pasts', function (Blueprint $table) {
            $table->string('playing_card_left_back')->nullable();
            $table->string('playing_card_center_back')->nullable();
            $table->string('playing_card_right_back')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fighter_users', function (Blueprint $table) {
            $table->dropColumn(['playing_card_left_back']);
            $table->dropColumn(['playing_card_center_back']);
            $table->dropColumn(['playing_card_right_back']);
        });
        Schema::table('fighter_pasts', function (Blueprint $table) {
            $table->dropColumn(['playing_card_left_back']);
            $table->dropColumn(['playing_card_center_back']);
            $table->dropColumn(['playing_card_right_back']);
        });
    }
}

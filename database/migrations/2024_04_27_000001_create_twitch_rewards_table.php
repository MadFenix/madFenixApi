<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitchRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitch_rewards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('twitch_api_reward_id');
            $table->string('name');
            $table->integer('points');
            $table->timestamps();
        });

        Schema::create('twitch_reward_redemptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('twitch_reward_id');
            $table->unsignedBigInteger('user_id');
            $table->string('twitch_api_reward_redemption_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitch_rewards');
        Schema::dropIfExists('twitch_reward_redemptions');
    }
}

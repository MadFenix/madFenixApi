<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->integer('season_level')->nullable()->after('oro');
            $table->integer('season_points')->nullable()->after('season_level');
        });

        Schema::create('nfts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('portrait_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('token_props');
            $table->integer('token_realm');
            $table->integer('token_number');
            $table->timestamps();
        });

        Schema::create('nft_identifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('nft_identification');
            $table->unsignedBigInteger('nft_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::table('blockchain_historicals', function (Blueprint $table) {
            $table->unsignedBigInteger('nft_identification_id')->nullable()->after('dragones_custodio');
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('max_level');
            $table->integer('max_points');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });

        Schema::create('season_rewards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('level');
            $table->integer('required_points');
            $table->integer('oro')->nullable();
            $table->integer('plumas')->nullable();
            $table->unsignedBigInteger('nft_id')->nullable();
            $table->integer('max_nft_rewards')->nullable();
            $table->string('custom_reward')->nullable();
            $table->unsignedBigInteger('season_id');
            $table->timestamps();
        });

        Schema::create('season_reward_redeemeds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('season_reward_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('blockchain_historical_id')->nullable();
            $table->unsignedBigInteger('nft_identification_id')->nullable();
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
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['season_points','season_level']);
        });
        Schema::dropIfExists('season_reward_redeemeds');
        Schema::dropIfExists('season_rewards');
        Schema::dropIfExists('seasons');
        Schema::table('blockchain_historicals', function (Blueprint $table) {
            $table->dropColumn(['nft_identification_id']);
        });
        Schema::dropIfExists('nft_identifications');
        Schema::dropIfExists('nfts');
    }
}

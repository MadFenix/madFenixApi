<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFightersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fighter_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('avatar_image')->nullable();
            $table->string('avatar_frame')->nullable();
            $table->string('action_frame')->nullable();
            $table->string('card_frame')->nullable();
            $table->string('game_arena')->nullable();
            $table->integer('cups')->nullable();
            $table->string('rank')->nullable();
            $table->integer('decks_available')->nullable();
            $table->integer('deck_current')->nullable();
            $table->string('deck_1')->nullable();
            $table->string('deck_2')->nullable();
            $table->string('deck_3')->nullable();
            $table->string('deck_4')->nullable();
            $table->string('deck_5')->nullable();
            $table->string('deck_6')->nullable();
            $table->string('deck_7')->nullable();
            $table->string('deck_8')->nullable();
            $table->string('deck_9')->nullable();
            $table->string('deck_10')->nullable();
            $table->boolean('ready_to_play')->nullable();
            $table->dateTime('ready_to_play_last')->nullable();
            $table->integer('playing_with_user')->nullable();
            $table->string('playing_deck')->nullable();
            $table->string('playing_hand')->nullable();
            $table->integer('playing_shift')->nullable();
            $table->integer('playing_hp')->nullable();
            $table->integer('playing_pa')->nullable();
            $table->string('playing_card_left')->nullable();
            $table->string('playing_card_center')->nullable();
            $table->string('playing_card_right')->nullable();
            $table->timestamps();
        });

        Schema::create('fighter_pasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('game_hash')->nullable();
            $table->string('avatar_image')->nullable();
            $table->string('avatar_frame')->nullable();
            $table->string('action_frame')->nullable();
            $table->string('card_frame')->nullable();
            $table->string('game_arena')->nullable();
            $table->integer('decks_available')->nullable();
            $table->integer('deck_current')->nullable();
            $table->boolean('ready_to_play')->nullable();
            $table->integer('playing_with_user')->nullable();
            $table->string('playing_deck')->nullable();
            $table->string('playing_hand')->nullable();
            $table->integer('playing_shift')->nullable();
            $table->integer('playing_hp')->nullable();
            $table->integer('playing_pa')->nullable();
            $table->string('playing_card_left')->nullable();
            $table->string('playing_card_center')->nullable();
            $table->string('playing_card_right')->nullable();
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
        Schema::dropIfExists('fighter_user');
        Schema::dropIfExists('fighter_pasts');
    }
}

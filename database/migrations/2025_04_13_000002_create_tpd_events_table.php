<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTpdEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tpd_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('nft_id')->nullable();
            $table->unsignedBigInteger('character_id')->nullable();
            $table->unsignedBigInteger('tpd_enemy_id')->nullable();
            $table->string('type')->nullable(); // Without effect v1.0
            $table->string('subtype')->nullable(); // Without effect v1.0
            $table->integer('active')->nullable();
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('portrait_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('tpd_entry_url')->nullable();
            $table->integer('act')->nullable();
            $table->string('actions')->nullable();
            $table->string('actions_rewards')->nullable();
            $table->string('actions_nft')->nullable();
            $table->string('actions_rewards_nft')->nullable();
            $table->string('answer_nft_common')->nullable();
            $table->string('answer_nft_uncommon')->nullable();
            $table->string('answer_nft_rare')->nullable();
            $table->string('answer_nft_legendary')->nullable();
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
        Schema::dropIfExists('tpd_events');
    }
}

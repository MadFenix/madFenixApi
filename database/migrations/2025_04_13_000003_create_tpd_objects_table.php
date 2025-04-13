<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTpdObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tpd_objects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('nft_id')->nullable();
            $table->unsignedBigInteger('character_id')->nullable();
            $table->string('type')->nullable(); // equipment, rune, relic, consumable
            $table->string('subtype')->nullable(); // equipment(head, armor, ring, gloves, shoes), rune(common, uncommon, rare, legendary), relic(common, uncommon, rare, legendary), consumable(none)
            $table->integer('active')->nullable();
            $table->integer('active_in_game_store')->nullable();
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('portrait_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('tpd_entry_url')->nullable();
            $table->integer('hp')->nullable();
            $table->integer('ad')->nullable();
            $table->integer('ap')->nullable();
            $table->integer('def')->nullable();
            $table->integer('mr')->nullable();
            $table->integer('act')->nullable();
            $table->string('actions')->nullable();
            $table->string('answer_nft_common')->nullable();
            $table->string('answer_nft_common_action')->nullable();
            $table->string('answer_nft_uncommon')->nullable();
            $table->string('answer_nft_uncommon_action')->nullable();
            $table->string('answer_nft_rare')->nullable();
            $table->string('answer_nft_rare_action')->nullable();
            $table->string('answer_nft_legendary')->nullable();
            $table->string('answer_nft_legendary_action')->nullable();
            $table->string('answer_common')->nullable();
            $table->string('answer_common_action')->nullable();
            $table->string('answer_uncommon')->nullable();
            $table->string('answer_uncommon_action')->nullable();
            $table->string('answer_rare')->nullable();
            $table->string('answer_rare_action')->nullable();
            $table->string('answer_legendary')->nullable();
            $table->string('answer_legendary_action')->nullable();
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
        Schema::dropIfExists('tpd_objects');
    }
}

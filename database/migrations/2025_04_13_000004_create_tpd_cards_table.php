<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTpdCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tpd_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('nft_id')->nullable();
            $table->unsignedBigInteger('character_id')->nullable();
            $table->string('type')->nullable(); // Without effect v1.0
            $table->string('subtype')->nullable(); // Without effect v1.0
            $table->integer('active')->nullable();
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
            $table->string('actions_improved')->nullable();
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
        Schema::dropIfExists('tpd_cards');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFightersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fighter_users', function (Blueprint $table) {
            $table->text('deck_1')->change();
            $table->text('deck_2')->change();
            $table->text('deck_3')->change();
            $table->text('deck_4')->change();
            $table->text('deck_5')->change();
            $table->text('deck_6')->change();
            $table->text('deck_7')->change();
            $table->text('deck_8')->change();
            $table->text('deck_9')->change();
            $table->text('deck_10')->change();
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
            $table->string('deck_1')->change();
            $table->string('deck_2')->change();
            $table->string('deck_3')->change();
            $table->string('deck_4')->change();
            $table->string('deck_5')->change();
            $table->string('deck_6')->change();
            $table->string('deck_7')->change();
            $table->string('deck_8')->change();
            $table->string('deck_9')->change();
            $table->string('deck_10')->change();
        });
    }
}

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
            $table->text('deck_1')->nullable()->change();
            $table->text('deck_2')->nullable()->change();
            $table->text('deck_3')->nullable()->change();
            $table->text('deck_4')->nullable()->change();
            $table->text('deck_5')->nullable()->change();
            $table->text('deck_6')->nullable()->change();
            $table->text('deck_7')->nullable()->change();
            $table->text('deck_8')->nullable()->change();
            $table->text('deck_9')->nullable()->change();
            $table->text('deck_10')->nullable()->change();
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
            $table->string('deck_1')->nullable()->change();
            $table->string('deck_2')->nullable()->change();
            $table->string('deck_3')->nullable()->change();
            $table->string('deck_4')->nullable()->change();
            $table->string('deck_5')->nullable()->change();
            $table->string('deck_6')->nullable()->change();
            $table->string('deck_7')->nullable()->change();
            $table->string('deck_8')->nullable()->change();
            $table->string('deck_9')->nullable()->change();
            $table->string('deck_10')->nullable()->change();
        });
    }
}

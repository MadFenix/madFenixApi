<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('portrait_image')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('answers')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });

        Schema::create('poll_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('poll_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plumas');
            $table->unsignedBigInteger('cronistas');
            $table->string('answer');
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
        Schema::dropIfExists('poll_answers');
        Schema::dropIfExists('polls');
    }
}

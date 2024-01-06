<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('creator_id')->index();
            $table->string('description');
            $table->text('details');
            $table->string('scene');
            $table->float('translation_x');
            $table->float('translation_y');
            $table->float('translation_z');
            $table->float('rotation_x');
            $table->float('rotation_y');
            $table->float('rotation_z');
            $table->float('scale_x');
            $table->float('scale_y');
            $table->float('scale_z');
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
        Schema::dropIfExists('nodes');
    }
}

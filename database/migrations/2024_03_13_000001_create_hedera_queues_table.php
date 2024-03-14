<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHederaQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hedera_queues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->integer('plumas')->nullable();
            $table->integer('piezas_de_oro_ft')->nullable();
            $table->text('piezas_de_oro_nft')->nullable();
            $table->text('dragones_custodio')->nullable();
            $table->string('id_hedera');
            $table->integer('attempts')->nullable();
            $table->boolean('done');
            $table->string('transaction_id')->nullable();
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
        Schema::dropIfExists('hedera_queues');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSeasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hedera_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('nft_identification_id')->nullable()->after('dragones_custodio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hedera_queues', function (Blueprint $table) {
            $table->dropColumn(['nft_identification_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update2NftIdentificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nft_identifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_hedera')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nft_identifications', function (Blueprint $table) {
            $table->dropColumn(['user_id_hedera']);
        });
    }
}

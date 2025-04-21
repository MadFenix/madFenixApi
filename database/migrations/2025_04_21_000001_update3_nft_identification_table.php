<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update3NftIdentificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nft_identifications', function (Blueprint $table) {
            $table->string('rarity')->nullable()->after('nft_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('rarity')->nullable()->after('nft_id');
            $table->integer('one_time_purchase')->nullable()->after('custom');
            $table->integer('one_time_purchase_global')->nullable()->after('one_time_purchase');
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
            $table->dropColumn(['rarity']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rarity', 'one_time_purchase', 'one_time_purchase_global']);
        });
    }
}

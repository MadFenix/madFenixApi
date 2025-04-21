<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProduct3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('nft_serial_greater_equal')->nullable()->after('rarity');
            $table->integer('nft_serial_less_equal')->nullable()->after('nft_serial_mayor_igual');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['nft_serial_greater_equal', 'nft_serial_less_equal']);
        });
    }
}

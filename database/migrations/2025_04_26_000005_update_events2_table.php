<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEvents2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['reservated_at']);
            $table->timestamp('start_at', 0)->nullable()->after('details');
            $table->timestamp('end_at', 0)->nullable()->after('start_at');
            $table->timestamp('read_at', 0)->nullable()->after('end_at');
            $table->integer('product_gift_delivered')->nullable()->after('read_at');
            $table->unsignedBigInteger('product_gift_id')->nullable()->after('product_gift_delivered');
        });
        Schema::table('product_orders', function (Blueprint $table) {
            $table->integer('is_gift')->nullable()->after('custom');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['start_at','end_at','read_at','product_gift_delivered','product_gift_id']);
            $table->timestamp('reservated_at', 0)->nullable()->after('details');
        });
        Schema::table('product_orders', function (Blueprint $table) {
            $table->dropColumn(['is_gift']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coupon');
            $table->integer('nft_id');
            $table->string('rarity')->nullable();
            $table->string('tags')->nullable();
            $table->integer('nft_serial_greater_equal')->nullable();
            $table->integer('nft_serial_less_equal')->nullable();
            $table->integer('uses');
            $table->integer('max_uses');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });

        Schema::create('coupon_item_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('coupon_items');
        Schema::dropIfExists('coupon_item_users');
    }
}

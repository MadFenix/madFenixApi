<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->float('price_fiat')->nullable();
            $table->integer('price_oro')->nullable();
            $table->integer('active')->nullable();
            $table->unsignedBigInteger('product_parent_id')->nullable();
            $table->integer('oro')->nullable();
            $table->integer('plumas')->nullable();
            $table->unsignedBigInteger('nft_id')->nullable();
            $table->string('custom')->nullable();
            $table->timestamps();
        });

        Schema::create('product_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('payment_validated')->nullable();
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
        Schema::dropIfExists('product_orders');
        Schema::dropIfExists('products');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->integer('oro')->after('plumas');
        });

        Schema::create('coupon_golds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('coupon');
            $table->integer('oro');
            $table->integer('uses');
            $table->integer('max_uses');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->timestamps();
        });

        Schema::create('coupon_gold_users', function (Blueprint $table) {
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
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['oro']);
        });
        Schema::dropIfExists('coupons_gold');
        Schema::dropIfExists('coupon_gold_users');
    }
}

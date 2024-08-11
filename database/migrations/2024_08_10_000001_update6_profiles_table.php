<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update6ProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('hedera_wallet')->unique()->nullable()->after('season_premium');
            $table->integer('hedera_wallet_check')->nullable()->after('hedera_wallet');
            $table->string('hedera_wallet_check_account')->nullable()->after('hedera_wallet_check');
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
            $table->dropColumn(['hedera_wallet','hedera_wallet_check','hedera_wallet_check_account']);
        });
    }
}

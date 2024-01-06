<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRuns2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('runs', function (Blueprint $table) {
            $table->dropColumn(['response']);
        });
        Schema::table('runs', function (Blueprint $table) {
            $table->text('response')->after('assistance_to_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('runs', function (Blueprint $table) {
            $table->dropColumn(['response']);
        });
        Schema::table('runs', function (Blueprint $table) {
            $table->string('response')->after('assistance_to_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssistances6Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assistances', function (Blueprint $table) {
            $table->boolean('conversation_blocked')->after('last_run_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assistances', function (Blueprint $table) {
            $table->dropColumn(['conversation_blocked']);
        });
    }
}

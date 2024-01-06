<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssistances5Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assistances', function (Blueprint $table) {
            $table->dropColumn(['lastResponse']);
            $table->string('last_run_id')->after('google_ai_voice_name');
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
            $table->string('lastResponse')->after('google_ai_voice_name');
            $table->dropColumn(['last_run_id']);
        });
    }
}

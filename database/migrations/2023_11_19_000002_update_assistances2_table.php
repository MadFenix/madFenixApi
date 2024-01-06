<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssistances2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assistances', function (Blueprint $table) {
            $table->string('google_ai_voice_name')->after('open_ai_assistant_id');
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
            $table->dropColumn(['google_ai_voice_name']);
        });
    }
}

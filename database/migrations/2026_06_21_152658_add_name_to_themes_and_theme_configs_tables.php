<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        Schema::table('theme_configs', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('theme_configs', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};

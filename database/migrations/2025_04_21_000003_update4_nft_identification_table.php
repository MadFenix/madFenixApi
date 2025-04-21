<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Update4NftIdentificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nft_identifications', function (Blueprint $table) {
            $table->string('tag_1')->nullable()->after('rarity');
            $table->string('tag_2')->nullable()->after('tag_1');
            $table->string('tag_3')->nullable()->after('tag_2');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('tags')->nullable()->after('rarity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nft_identifications', function (Blueprint $table) {
            $table->dropColumn(['tag_1', 'tag_2', 'tag_3']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tags']);
        });
    }
}

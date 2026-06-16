<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObsToUnities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_unity', function (Blueprint $table) {
            $table->string('observacoes', 500)->nullable();
        });

        Schema::table('kit_unity', function (Blueprint $table) {
            $table->string('observacoes', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_unity', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });

        Schema::table('kit_unity', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });
    }
}

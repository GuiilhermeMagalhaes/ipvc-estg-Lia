<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveQuantityDispFromItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('item', function (Blueprint $table) {
        if (Schema::hasColumn('item', 'quantity_disp')) {
            $table->dropColumn('quantity_disp');
        }
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::table('item', function (Blueprint $table) {
            $table->integer('quantity_disp');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceDayToItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('item', function (Blueprint $table) {
        $table->decimal('price_day', 8, 2)->nullable()->after('preco');
    });
}

public function down()
{
    Schema::table('item', function (Blueprint $table) {
        $table->dropColumn('price_day');
    });
}
}

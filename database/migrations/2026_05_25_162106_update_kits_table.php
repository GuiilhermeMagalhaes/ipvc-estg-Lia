<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('kits', function (Blueprint $table) {

            // remover foreign key primeiro
            $table->dropForeign('kits_kit_state_id_foreign');

            // remover colunas antigas
            $table->dropColumn('kit_state_id');
            $table->dropColumn('lia_code');

            // novas colunas
            $table->double('price_day', 8, 2)->after('price');
            $table->integer('quantity')->after('price_day');
            $table->integer('quantity_disp')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema::table('kits', function (Blueprint $table) {

            // remover novas colunas
            $table->dropColumn(['price_day', 'quantity', 'quantity_disp']);

            // repor colunas antigas
            $table->unsignedBigInteger('kit_state_id')->after('price');
            $table->string('lia_code', 191)->nullable()->after('name');

            // repor foreign key
            $table->foreign('kit_state_id')
                  ->references('id')
                  ->on('kit_states');
        });
    }
}

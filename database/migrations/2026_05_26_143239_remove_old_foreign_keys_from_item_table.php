<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldForeignKeysFromItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item', function (Blueprint $table) {
            
            $table->dropForeign(['item_state_id']);

            $table->dropForeign(['kit_id']);

            // remover colunas
            $table->dropColumn([
                'item_state_id',
                'kit_id'
            ]);
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
                   // recriar colunas
            $table->unsignedBigInteger('item_state_id');

            $table->unsignedBigInteger('kit_id')
                  ->nullable();

            // recriar foreign keys
            $table->foreign('item_state_id')
                  ->references('id')
                  ->on('item_states');

            $table->foreign('kit_id')
                  ->references('id')
                  ->on('kits');
        });
    }
}

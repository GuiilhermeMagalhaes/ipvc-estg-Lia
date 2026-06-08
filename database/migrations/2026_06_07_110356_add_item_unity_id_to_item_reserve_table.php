<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemUnityIdToItemReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_reserve', function (Blueprint $table) {
        // Adiciona a coluna para ligar à unidade física específica
        $table->unsignedBigInteger('item_unity_id')->nullable()->after('item_id');
        
        // Cria a relação oficial com a tabela item_unity
        $table->foreign('item_unity_id')->references('id')->on('item_unity')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('item_reserve', function (Blueprint $table) {
        $table->dropForeign(['item_unity_id']);
        $table->dropColumn('item_unity_id');
    });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataAquisicaoToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('item', function (Blueprint $table) {
        // Adiciona a coluna do tipo 'date' (pode ser nula para os itens antigos onde não sabem a data)
        $table->date('data_aquisicao')->nullable()->after('preco'); 
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
            $table->dropColumn('data_aquisicao');
        });
    }
}

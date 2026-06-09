<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToKitReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
            Schema::table('kit_reserve', function (Blueprint $table) {
            // Cria a restrição de chave estrangeira (Foreign Key) para o kit_id
            $table->foreign('kit_id')
                  ->references('id')
                  ->on('kits')
                  ->onDelete('cascade'); // Se o kit for apagado, remove os registos desta tabela
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kit_reserve', function (Blueprint $table) {
            $table->dropForeign(['kit_id']);
        });
    }
}

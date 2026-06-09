<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixKitIdForeignOnKitReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // 1. Elimina a coluna kit_unity_id se ela existir
        if (Schema::hasColumn('kit_reserve', 'kit_unity_id')) {
            DB::statement('ALTER TABLE kit_reserve DROP COLUMN kit_unity_id');
        }

        // 2. Garante que a coluna kit_id existe na tabela para guardares o ID do kit
        if (!Schema::hasColumn('kit_reserve', 'kit_id')) {
            Schema::table('kit_reserve', function (Blueprint $table) {
                $table->unsignedBigInteger('kit_id')->after('reserve_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('kit_reserve', function (Blueprint $table) {
            // 1. Se a coluna kit_id existir, remove-a
            if (Schema::hasColumn('kit_reserve', 'kit_id')) {
                $table->dropColumn('kit_id');
            }

            // 2. Cria novamente a coluna kit_unity_id para voltar ao estado anterior
            if (!Schema::hasColumn('kit_reserve', 'kit_unity_id')) {
                $table->unsignedBigInteger('kit_unity_id')->after('reserve_id');
            }
        });
    
    }
}

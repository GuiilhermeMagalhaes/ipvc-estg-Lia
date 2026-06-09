<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToKitReserveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
            Schema::table('kit_reserve', function (Blueprint $table) {
        if (!Schema::hasColumn('kit_reserve', 'kit_id')) {
            // Criamos apenas a coluna sem a restrição da FK imediatamente, para não prender
            $table->unsignedBigInteger('kit_id')->nullable()->after('reserve_id');
        }

        if (!Schema::hasColumn('kit_reserve', 'quantity')) {
            $table->integer('quantity')->default(1)->after('reserve_id');
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
       Schema::table('kit_reserve', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}

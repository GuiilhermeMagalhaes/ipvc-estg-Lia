<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDataAquisicaoFromItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('item') && Schema::hasColumn('item', 'data_aquisicao')) {
            Schema::table('item', function (Blueprint $table) {
                $table->dropColumn('data_aquisicao');
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
        if (Schema::hasTable('item') && !Schema::hasColumn('item', 'data_aquisicao')) {
            Schema::table('item', function (Blueprint $table) {
                $table->date('data_aquisicao')->nullable(); 
            });
        }
    }
}

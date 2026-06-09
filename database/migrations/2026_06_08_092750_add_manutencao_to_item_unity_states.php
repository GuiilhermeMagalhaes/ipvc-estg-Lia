<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManutencaoToItemUnityStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('item_unity_states')->insert([
        'description' => 'manutencao',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_unity_states', function (Blueprint $table) {
            DB::table('item_unity_states')->where('description', 'manutencao')->delete();
        });
    }
}

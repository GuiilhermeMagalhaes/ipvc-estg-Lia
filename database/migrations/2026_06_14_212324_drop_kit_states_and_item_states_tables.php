<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropKitStatesAndItemStatesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('item') && Schema::hasColumn('item', 'item_state_id')) {
            Schema::table('item', function (Blueprint $table) {
                $table->dropForeign(['item_state_id']); 
                $table->dropColumn('item_state_id');   
            });
        }

        
        if (Schema::hasTable('kits') && Schema::hasColumn('kits', 'kit_state_id')) {
            Schema::table('kits', function (Blueprint $table) {
                $table->dropForeign(['kit_state_id']); 
                $table->dropColumn('kit_state_id');   
            });
        }

        
        Schema::dropIfExists('kit_states');
        Schema::dropIfExists('item_states');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::create('kit_states', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('item_states', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });

        Schema::table('item', function (Blueprint $table) {
            $table->foreignId('item_state_id')->nullable()->constrained('item_states');
        });

        Schema::table('kits', function (Blueprint $table) {
            $table->foreignId('kit_state_id')->nullable()->constrained('kit_states');
        });
    }
}

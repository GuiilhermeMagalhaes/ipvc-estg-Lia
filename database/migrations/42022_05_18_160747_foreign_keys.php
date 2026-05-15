<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->foreign('user_status_id')->references('id')->on('user_statuses');
            $table->foreign('user_type_id')->references('id')->on('user_types');
        });
        Schema::table('cost_center_user', function(Blueprint $table) {
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('item', function(Blueprint $table) {
            $table->foreign('categoria_id')->references('id')->on('item_categories');
            $table->foreign('item_state_id')->references('id')->on('item_states');
            $table->foreign('kit_id')->references('id')->on('kits');
        });
        Schema::table('item_reserve', function(Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('item');
            $table->foreign('reserve_id')->references('id')->on('reserves');
        });
        Schema::table('reserves', function(Blueprint $table) {
            $table->foreign('ciclica_id')->references('id')->on('ciclica');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->foreign('reserve_state_id')->references('id')->on('reserve_states');
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('space_items', function(Blueprint $table) {
            $table->foreign('lia_space_id')->references('id')->on('lia_spaces');
        });
        Schema::table('kit_reserve', function(Blueprint $table) {
            $table->foreign('kit_id')->references('id')->on('kits');
            $table->foreign('reserve_id')->references('id')->on('reserves');
        });
        Schema::table('lia_space_space_reserve', function(Blueprint $table) {
            $table->foreign('lia_space_id')->references('id')->on('lia_spaces');
            $table->foreign('space_reserve_id')->references('id')->on('space_reserves');
        });
        Schema::table('space_reserve_user', function(Blueprint $table) {
            $table->foreign('space_reserve_id')->references('id')->on('space_reserves');
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('kits', function(Blueprint $table){
            $table->foreign('kit_state_id')->references('id')->on('kit_states');
        });
        Schema::table('space_reserves', function(Blueprint $table){
            $table->foreign('occupant_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

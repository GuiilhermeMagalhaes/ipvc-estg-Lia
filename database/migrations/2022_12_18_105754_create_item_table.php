<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ipvc_ref')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('nome');
            $table->string('model')->nullable();
            $table->float('preco');
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('item_state_id');
            $table->unsignedBigInteger('kit_id')->nullable();
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item');
    }
}

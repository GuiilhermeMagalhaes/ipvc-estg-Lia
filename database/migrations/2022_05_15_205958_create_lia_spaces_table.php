<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiaSpacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lia_spaces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->string('pc');
            $table->string('teclado');
            $table->string('rato');
            $table->float('cost');
            $table->string('lia_code')->nullable();
            $table->integer('space_code')->nullable();
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
        Schema::dropIfExists('lia_spaces');
    }
}

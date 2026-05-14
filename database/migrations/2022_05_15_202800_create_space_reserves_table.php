<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpaceReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('space_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->float('cost');
            $table->unsignedBigInteger('occupant_id');
            $table->integer('space_code');
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
        Schema::dropIfExists('space_reserves');
    }
}

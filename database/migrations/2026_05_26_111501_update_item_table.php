<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item', function (Blueprint $table) {

            $table->dropColumn('lia_code');

            $table->double('price_day', 8, 2);
                  
            $table->integer('quantity');
                  
            $table->integer('quantity_disp');
                  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('item', function (Blueprint $table) {

            $table->dropColumn([
                'price_day',
                'quantity',
                'quantity_disp'
            ]);


            $table->string('lia_code')
                  ->nullable();
        });
    }
}

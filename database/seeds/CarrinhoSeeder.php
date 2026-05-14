<?php

use Illuminate\Database\Seeder;

class CarrinhoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('carrinho_compras')->insert([
            'id' => 1,
            'user_id'=> 1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        
         
    }
}

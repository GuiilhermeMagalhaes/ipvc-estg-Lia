<?php

use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categorias')->insert([
            'name' => 'Default',
            'descricao'=> 'Categoria Geral',
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        
    }
}

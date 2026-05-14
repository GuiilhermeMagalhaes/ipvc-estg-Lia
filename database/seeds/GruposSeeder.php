<?php

use Illuminate\Database\Seeder;

class GruposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('grupos')->insert([
            'name' => 'Admin',
            'admin'=> true,
            'gerirReservas'=>true,
            'gerirItemsKits'=>true,
            'gerirGrupos'=>true,
            'gerirCategorias'=>true,
            'gerirCentros'=>true,
            'gerirUsers'=>true,
            'reservar'=>true


        ]);

        DB::table('grupos')->insert([
            'name' => 'default',
            'admin'=> false,
            'gerirReservas'=>false,
            'gerirItemsKits'=>false,
            'gerirGrupos'=>false,
            'gerirCategorias'=>false,
            'gerirCentros'=>false,
            'gerirUsers'=>false,
            'reservar'=>false

        ]);
        DB::table('grupos')->insert([
            'name' => 'Reservar',
            'admin'=> false,
            'gerirReservas'=>false,
            'gerirItemsKits'=>false,
            'gerirGrupos'=>false,
            'gerirCategorias'=>false,
            'gerirCentros'=>false,
            'gerirUsers'=>false,
            'reservar'=>true

        ]);
        
    }

}

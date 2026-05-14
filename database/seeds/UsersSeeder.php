<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $grupoAdmin = DB::table('grupos')->select('id')->where('name', '=', 'Admin')->first();
        $grupoDefault = DB::table('grupos')->select('id')->where('name', '=', 'default')->first();
        $grupoReserva = DB::table('grupos')->select('id')->where('name', '=', 'Reservar')->first();


        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'lia@estg.ipvc.pt',
            'password' => Hash::make('password'),
            'numero_mecanografico' => '1',
            'numero_telemovel' => '220220210',
            'grupo_id' => $grupoAdmin->id
        ]);
        
    }
}

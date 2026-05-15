<?php

use Illuminate\Database\Seeder;

class KitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*
        DB::table('atributos')->insert([
            'marca' => 'Canon ',
            'modelo'=> 'Kit',
            'serial_number'=> '200321232',
            'descricao'=>'Maquinas de Calcular',
            'fotografia_caminho'=>'/uploads/images/camara-canon_1580116571.png',
            'visivel'=> 1,
            'quantidade'=>11,
            'is_item'=>0,
            'is_operacional'=>1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('atributos')->insert([
            'marca' => 'Canon ',
            'modelo'=> 'Kit',
            'serial_number'=> '200232',
            'descricao'=>'Maquinas Fotograficas',
            'fotografia_caminho'=>'/uploads/images/camara-canon_1580116571.png',
            'visivel'=> 1,
            'quantidade'=>11,
            'is_item'=>0,
            'is_operacional'=>1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('atributos')->insert([
            'marca' => 'Canon Stream',
            'modelo'=> 'Kit',
            'serial_number'=> '200231213132',
            'descricao'=>'Maquinas Fotograficas',
            'fotografia_caminho'=>'/uploads/images/camara-canon_1580116571.png',
            'visivel'=> 1,
            'quantidade'=>11,
            'is_operacional'=>1,
            'is_item'=>0,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('atributos')->insert([
            'marca' => 'HP',
            'modelo'=> 'Kit',
            'serial_number'=> '200212454654312',
            'descricao'=>'Computadores',
            'fotografia_caminho'=>'/uploads/images/camara-canon_1580116571.png',
            'visivel'=> 1,
            'quantidade'=>11,
            'is_operacional'=>1,
            'is_item'=>1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-1",
            'name' => 'Kit Streaming',
            'categoria_id'=> '1',
            'id_atributos'=>'5',
            
            
        ]);
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-2",
            'name' => 'Kit Calculadoras',
            'categoria_id'=> '2',
            'id_atributos'=>'6',
            
            
        ]);

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-3",
            'name' => 'Kit Cameras',
            'categoria_id'=> '1',
            'id_atributos'=>'6',
            
            
        ]);

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-4",
            'name' => 'Kit Computadores',
            'categoria_id'=> '1',
            'id_atributos'=>'7',
            
            
        ]);
        */

        $i = 1;
        DB::table('atributos')->insert([
            'marca' => 'none kit',
            'modelo'=> 'none kit ',
            'serial_number'=> 2,
            'descricao'=>'none kit',
            'fotografia_caminho'=>'/uploads/images/default.png',
            'visivel'=> 1,
            'is_item'=>1,
            'is_operacional'=>1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);


        //Malas
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Video Sony HXR-NX100',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            
            
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Video Sony HXR-NX100',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            
            
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Video Sony HXR-NX100',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            ]);
        $i++;
        //  !Malas


        //Malas Iluminação

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Iluminação',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            
            
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Iluminação',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            
            
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mala Iluminação',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
            
            
        ]);
        $i++;
        //Mochila Fotografia

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;

        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        DB::table('kits')->insert([
            'id_ipvc'=> "Lia-k-".$i,
            'name' => 'Mochila Lowepro',
            'categoria_id'=> '1',
            'id_atributos'=>'1',
        ]);
        $i++;
        // fim mochila fotografia

    }
}

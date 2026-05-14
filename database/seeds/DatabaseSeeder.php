<?php

use App\Atributo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(GruposSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(CategoriaSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(KitSeeder::class);
        $this->call(CarrinhoSeeder::class);
        $this->call(ReservaSeeder::class);
        $this->call(ReservaLinhasSeeder::class);
        $this->call(CentroCustosSeeder::class);
        $this->call(CentroCustosLinha::class);
        $this->call(TimeSeeder::class);
        

    }
}

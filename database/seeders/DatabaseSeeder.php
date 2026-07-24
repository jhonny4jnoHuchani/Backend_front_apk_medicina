<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsuariosSeeder::class,
            MateriasSeeder::class,
            ParalelosSeeder::class,
            UbicacionesSeeder::class,
            AsignacionesSeeder::class,
            HorariosSeeder::class,
        ]);
    }
}
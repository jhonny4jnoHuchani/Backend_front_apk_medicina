<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ubicacion;

class UbicacionesSeeder extends Seeder
{
    public function run(): void
    {
        Ubicacion::create([
            'nombre_lugar'    => 'Aula 101',
            'tipo'            => 'aula',
            'edificio_campus' => 'Campus Central',
            'coordenadas'     => [
                ['lat' => -25.2810, 'lon' => -57.6350],
                ['lat' => -25.2810, 'lon' => -57.6360],
                ['lat' => -25.2820, 'lon' => -57.6360],
                ['lat' => -25.2820, 'lon' => -57.6350],
            ],
            'tolerancia_metros' => 50,
        ]);

        Ubicacion::create([
            'nombre_lugar'    => 'Laboratorio Física',
            'tipo'            => 'laboratorio',
            'edificio_campus' => 'Campus Central',
            'coordenadas'     => [
                ['lat' => -25.2815, 'lon' => -57.6355],
                ['lat' => -25.2815, 'lon' => -57.6365],
                ['lat' => -25.2825, 'lon' => -57.6365],
                ['lat' => -25.2825, 'lon' => -57.6355],
            ],
            'tolerancia_metros' => 50,
        ]);

        echo "✅ 2 ubicaciones creadas.\n";
    }
}
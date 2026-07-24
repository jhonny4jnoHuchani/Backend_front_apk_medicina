<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paralelo;

class ParalelosSeeder extends Seeder
{
    public function run(): void
    {
        Paralelo::create(['grado' => 1, 'paralelo' => 'A', 'capacidad' => 30]);
        Paralelo::create(['grado' => 1, 'paralelo' => 'B', 'capacidad' => 25]);

        echo "✅ 2 paralelos creados.\n";
    }
}
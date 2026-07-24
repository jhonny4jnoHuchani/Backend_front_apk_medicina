<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;

class MateriasSeeder extends Seeder
{
    public function run(): void
    {
        Materia::create(['nombre_materia' => 'Matemáticas I',  'codigo' => 'MAT101', 'creditos' => 4, 'nivel' => 1]);
        Materia::create(['nombre_materia' => 'Física I',       'codigo' => 'FIS101', 'creditos' => 3, 'nivel' => 1]);
        Materia::create(['nombre_materia' => 'Programación I', 'codigo' => 'PRO101', 'creditos' => 3, 'nivel' => 1]);
        Materia::create(['nombre_materia' => 'Química I',      'codigo' => 'QUI101', 'creditos' => 4, 'nivel' => 1]);

        echo "✅ 4 materias creadas.\n";
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParaleloMateria;

class AsignacionesSeeder extends Seeder
{
    public function run(): void
    {
        $docenteId = 1;

        ParaleloMateria::create(['materia_id' => 1, 'paralelo_id' => 1, 'docente_id' => $docenteId]); // Mat - 1A
        ParaleloMateria::create(['materia_id' => 2, 'paralelo_id' => 1, 'docente_id' => $docenteId]); // Fis - 1A
        ParaleloMateria::create(['materia_id' => 3, 'paralelo_id' => 2, 'docente_id' => $docenteId]); // Pro - 1B
        ParaleloMateria::create(['materia_id' => 4, 'paralelo_id' => 2, 'docente_id' => $docenteId]); // Qui - 1B

        echo "✅ 4 asignaciones creadas.\n";
    }
}
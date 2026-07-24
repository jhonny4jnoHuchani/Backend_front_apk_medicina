<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Horario;

class HorariosSeeder extends Seeder
{
    public function run(): void
    {
        Horario::create(['paralelo_materia_id' => 1, 'ubicacion_id' => 1, 'dia_semana' => 'lunes',     'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'tipo_actividad' => 'clase']);
        Horario::create(['paralelo_materia_id' => 2, 'ubicacion_id' => 1, 'dia_semana' => 'martes',    'hora_inicio' => '10:00', 'hora_fin' => '12:00', 'tipo_actividad' => 'clase']);
        Horario::create(['paralelo_materia_id' => 3, 'ubicacion_id' => 2, 'dia_semana' => 'miercoles', 'hora_inicio' => '14:00', 'hora_fin' => '16:00', 'tipo_actividad' => 'laboratorio']);
        Horario::create(['paralelo_materia_id' => 4, 'ubicacion_id' => 2, 'dia_semana' => 'viernes',   'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'tipo_actividad' => 'clase']);
        Horario::create(['paralelo_materia_id' => 4, 'ubicacion_id' => 2, 'dia_semana' => 'viernes',   'hora_inicio' => '00:38', 'hora_fin' => '01:15', 'tipo_actividad' => 'clase']);

        echo "✅ 5 horarios creados.\n";
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'paralelo_materia_id',
        'ubicacion_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'tipo_actividad',
        'estado',
    ];

    public function paraleloMateria()
    {
        return $this->belongsTo(ParaleloMateria::class, 'paralelo_materia_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function marcados()
    {
        return $this->hasMany(Marcado::class, 'horario_id');
    }
}
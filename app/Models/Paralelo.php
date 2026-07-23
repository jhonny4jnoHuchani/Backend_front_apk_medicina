<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paralelo extends Model
{
    protected $table = 'paralelos';

    protected $fillable = [
        'grado',
        'paralelo',
        'capacidad',
        'estado',
    ];

    // N:M con Materia a través de paralelo_materia
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'paralelo_materia', 'paralelo_id', 'materia_id')
                    ->withPivot('docente_id');
    }

    // N:M con Docente a través de paralelo_materia
    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'paralelo_materia', 'paralelo_id', 'docente_id')
                    ->withPivot('materia_id');
    }

    public function paraleloMaterias()
    {
        return $this->hasMany(ParaleloMateria::class, 'paralelo_id');
    }
}
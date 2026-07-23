<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materia';

    protected $fillable = [
        'nombre_materia',
        'codigo',
        'creditos',
        'nivel',
        'modalidad',
        'estado',
    ];

    // N:M con Paralelo a través de paralelo_materia
    public function paralelos()
    {
        return $this->belongsToMany(Paralelo::class, 'paralelo_materia', 'materia_id', 'paralelo_id')
                    ->withPivot('docente_id');
    }

    public function paraleloMaterias()
    {
        return $this->hasMany(ParaleloMateria::class, 'materia_id');
    }
}
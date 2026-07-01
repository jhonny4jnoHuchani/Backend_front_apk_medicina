<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paralelo extends Model
{
    protected $table = 'paralelos';

    protected $fillable = [
        'materia_id',
        'docente_id',
        'grado',
        'paralelo',
        'capacidad',
        'estado',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'paralelo_id');
    }
}

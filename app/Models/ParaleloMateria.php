<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParaleloMateria extends Model
{
    protected $table = 'paralelo_materia';

    public $timestamps = false; // solo tiene created_at

    protected $fillable = [
        'materia_id',
        'paralelo_id',
        'docente_id',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function paralelo()
    {
        return $this->belongsTo(Paralelo::class, 'paralelo_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'paralelo_materia_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docente';

    protected $fillable = [
        'id_user',
        'departamento',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // N:M con Paralelo a través de paralelo_materia
    public function paralelos()
    {
        return $this->belongsToMany(Paralelo::class, 'paralelo_materia', 'docente_id', 'paralelo_id')
                    ->withPivot('materia_id');
    }

    public function paraleloMaterias()
    {
        return $this->hasMany(ParaleloMateria::class, 'docente_id');
    }

    public function marcados()
    {
        return $this->hasMany(Marcado::class, 'docente_id');
    }

    public function reconocimientoFacial()
    {
        return $this->hasOne(ReconocimientoFacial::class, 'id_docente');
    }

    public function embeddingsFaciales()
    {
        return $this->hasMany(EmbeddingFacial::class, 'id_docente');
    }

    public function logsReconocimiento()
    {
        return $this->hasMany(LogReconocimiento::class, 'docente_id');
    }
}
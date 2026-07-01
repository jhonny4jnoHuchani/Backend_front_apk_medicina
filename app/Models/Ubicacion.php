<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicacion';



    protected $fillable = [
        'nombre_lugar',
        'tipo',
        'edificio_campus',
        'coordenadas',
        'tolerancia_metros',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'coordenadas' => 'array',
        ];
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'ubicacion_id');
    }

    public function marcados()
    {
        return $this->hasMany(Marcado::class, 'ubicacion_id');
    }
}

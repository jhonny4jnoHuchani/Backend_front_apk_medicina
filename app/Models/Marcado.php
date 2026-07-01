<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marcado extends Model
{
    protected $table = 'marcados';

    const UPDATED_AT = null;

    protected $fillable = [
        'docente_id',
        'horario_id',
        'ubicacion_id',
        'fecha',
        'hora_inicio',
        'hora_final',
        'tipo_marcado',
        'latitud',
        'longitud',
        'foto_constancia',
        'estado',
        'observacion',
        'estado_asistencia',
        'minutos_retraso',
        'minutos_trabajados',
        'offline',
        'sincronizacion_offline',
        'fecha_dispositivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha'                  => 'date',
            'offline'                => 'boolean',
            'sincronizacion_offline' => 'boolean',
            'fecha_dispositivo'      => 'datetime',
            'latitud'                => 'decimal:8',
            'longitud'               => 'decimal:8',
        ];
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function permanencia()
    {
        return $this->hasOne(Permanencia::class, 'id_marcados');
    }
}

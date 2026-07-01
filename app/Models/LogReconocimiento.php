<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogReconocimiento extends Model
{
    protected $table = 'log_reconocimiento';


    protected $fillable = [
        'docente_id',
        'confianza',
        'resultado',
        'liveness_score',
        'ip_origen',
        'dispositivo_id',
        'imagen_captura',
        'tiempo_proceso_ms',
    ];

    protected function casts(): array
    {
        return [
            'confianza'      => 'decimal:2',
            'liveness_score' => 'decimal:3',
        ];
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
}

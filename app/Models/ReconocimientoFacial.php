<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconocimientoFacial extends Model
{
    protected $table = 'reconocimiento_facial';

    // Esta tabla sí tiene created_at y updated_at (usa timestamps() en la migración)

    protected $fillable = [
        'id_docente',
        'activo',
        'total_embeddings',
        'calidad_promedio',
    ];

    protected function casts(): array
    {
        return [
            'activo'           => 'boolean',
            'calidad_promedio' => 'decimal:3',
        ];
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function embeddings()
    {
        return $this->hasMany(EmbeddingFacial::class, 'reconocimiento_facial_id');
    }
}

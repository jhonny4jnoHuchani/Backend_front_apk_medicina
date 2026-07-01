<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbeddingFacial extends Model
{
    protected $table = 'embeddings_faciales';

    protected $fillable = [
        'reconocimiento_facial_id',
        'id_docente',
        'embedding',
        'quality_score',
        'posicion',
    ];

    protected function casts(): array
    {
        return [
            'quality_score' => 'decimal:3',
        ];
    }

    public function reconocimientoFacial()
    {
        return $this->belongsTo(ReconocimientoFacial::class, 'reconocimiento_facial_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }
}

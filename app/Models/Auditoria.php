<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';


    protected $fillable = [
        'usuario_id',
        'accion',
        'entidad',
        'entidad_id',
        'datos',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

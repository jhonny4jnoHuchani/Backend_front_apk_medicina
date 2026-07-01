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

    public function paralelos()
    {
        return $this->hasMany(Paralelo::class, 'materia_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permanencia extends Model
{
    protected $table = 'permanencia';

    public $timestamps = false;

    protected $fillable = [
        'id_marcados',
    ];

    public function marcado()
    {
        return $this->belongsTo(Marcado::class, 'id_marcados');
    }
}

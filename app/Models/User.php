<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'rol',
        'ci',
        'nombre_completo',
        'estado',
        'primer_login',
        'fecha_bloqueo',
        'ultimo_acceso',
        'device_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'primer_login'      => 'boolean',
            'fecha_bloqueo'     => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Un usuario puede tener un perfil de docente asociado
    public function docente()
    {
        return $this->hasOne(Docente::class, 'id_user');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }
}

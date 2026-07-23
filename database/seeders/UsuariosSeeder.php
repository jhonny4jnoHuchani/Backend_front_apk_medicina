<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Docente;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN ──────────────────────────────────
        $admin = User::create([
            'email'           => 'admin@sistema.com',
            'password'        => 'admin123',
            'rol'             => 'admin',
            'ci'              => '0000001',
            'nombre_completo' => 'Administrador Sistema',
            'estado'          => 'activo',
            'primer_login'    => true,
        ]);

        // ── DOCENTE ────────────────────────────────
        $docenteUser = User::create([
            'email'           => 'docente@sistema.com',
            'password'        => 'docente123',
            'rol'             => 'docente',
            'ci'              => '1234567',
            'nombre_completo' => 'Juan Pérez',
            'estado'          => 'activo',
            'primer_login'    => true,
        ]);

        Docente::create([
            'id_user'      => $docenteUser->id,
            'departamento' => 'Ciencias Básicas',
            'estado'       => 'activo',
        ]);

        echo "✅ Usuarios creados:\n";
        echo "   Admin:   admin@sistema.com / admin123\n";
        echo "   Docente: docente@sistema.com / docente123\n";
    }
}
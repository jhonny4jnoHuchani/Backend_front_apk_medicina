<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->enum('rol', ['admin', 'docente', 'supervisor'])->default('docente');
            $table->string('ci', 20)->unique();
            $table->string('nombre_completo', 200);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->boolean('primer_login')->default(true);
            $table->timestamp('fecha_bloqueo')->nullable();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->string('device_id', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paralelo_id')
                ->constrained('paralelos')
                ->restrictOnDelete();
            $table->foreignId('ubicacion_id')
                ->constrained('ubicacion')
                ->restrictOnDelete();
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('tipo_actividad', ['clase', 'laboratorio', 'tutoria', 'otro'])->default('clase');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->timestamps();
        });

        // Evita horarios donde la hora final sea menor o igual a la de inicio
        DB::statement('ALTER TABLE horarios ADD CONSTRAINT chk_horario_rango CHECK (hora_fin > hora_inicio)');
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};

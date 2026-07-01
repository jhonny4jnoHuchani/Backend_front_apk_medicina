<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                ->constrained('docente')
                ->restrictOnDelete();
            $table->foreignId('horario_id')
                ->constrained('horarios')
                ->restrictOnDelete();
            $table->foreignId('ubicacion_id')
                ->constrained('ubicacion')
                ->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_final')->nullable();
            $table->enum('tipo_marcado', ['entrada', 'salida']);
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->string('foto_constancia', 500)->nullable()
                ->comment('Foto comprimida del entorno al momento del marcado (evidencia, no biometría)');
            $table->enum('estado', ['valido', 'invalido', 'pendiente'])->default('pendiente');
            $table->text('observacion')->nullable();
            // Asistencia
            $table->enum('estado_asistencia', ['puntual', 'retraso', 'falta'])->nullable();
            $table->smallInteger('minutos_retraso')->nullable()->default(0);
            $table->smallInteger('minutos_trabajados')->nullable()->default(0);
            // Offline
            $table->boolean('offline')->default(false);
            $table->boolean('sincronizacion_offline')->default(false);
            $table->timestamp('fecha_dispositivo')->nullable();
            

            $table->index(['docente_id', 'fecha'], 'idx_marcados_docente_fecha');
            $table->index('horario_id', 'idx_marcados_horario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marcados');
    }
};

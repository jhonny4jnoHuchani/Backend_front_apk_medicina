<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_reconocimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                ->nullable()
                ->constrained('docente')
                ->nullOnDelete();
            $table->decimal('confianza', 5, 2)->default(0.00);
            $table->enum('resultado', ['reconocido', 'desconocido', 'spoofing_detectado']);
            $table->decimal('liveness_score', 4, 3)->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->string('dispositivo_id', 255)->nullable();
            $table->string('imagen_captura', 500)->nullable()
                ->comment('Solo se guarda en casos sospechosos (spoofing)');
            $table->smallInteger('tiempo_proceso_ms')->nullable();
            $table->timestamps();

            $table->index('docente_id', 'idx_log_docente');
            $table->index('created_at', 'idx_log_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_reconocimiento');
    }
};

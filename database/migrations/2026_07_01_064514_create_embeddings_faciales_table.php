<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sin UNIQUE por posición a propósito: cada docente tiene ~50 embeddings
        // repartidos en 6 posiciones (varias capturas por posición).
        Schema::create('embeddings_faciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconocimiento_facial_id')
                ->constrained('reconocimiento_facial')
                ->cascadeOnDelete();
            $table->foreignId('id_docente')
                ->constrained('docente')
                ->restrictOnDelete();
            $table->binary('embedding')
                ->comment('Vector float32 de 128 dimensiones serializado con numpy (512 bytes)');
            $table->decimal('quality_score', 4, 3)->default(0.000);
            $table->enum('posicion', ['centro', 'izquierda', 'derecha', 'arriba', 'abajo', 'sonrisa']);
            $table->timestamps();

            $table->index('id_docente', 'idx_embeddings_docente');
            $table->index('reconocimiento_facial_id', 'idx_embeddings_reconocimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings_faciales');
    }
};

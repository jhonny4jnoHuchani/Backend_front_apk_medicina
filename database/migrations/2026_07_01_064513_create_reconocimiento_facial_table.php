<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconocimiento_facial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_docente')
                ->unique()
                ->constrained('docente')
                ->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->smallInteger('total_embeddings')->default(0)
                ->comment('Mínimo 50 para habilitar reconocimiento');
            $table->decimal('calidad_promedio', 4, 3)->default(0.000)
                ->comment('Promedio de quality_score: 0.000 a 1.000');
            // timestamps() crea created_at y updated_at; Eloquent actualiza
            // updated_at automáticamente en cada save(), no hace falta trigger de BD.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconocimiento_facial');
    }
};

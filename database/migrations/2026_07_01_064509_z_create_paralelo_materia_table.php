<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paralelo_materia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('materia_id')
                ->constrained('materia')
                ->restrictOnDelete();

            $table->foreignId('paralelo_id')
                ->constrained('paralelos')
                ->restrictOnDelete();

            $table->foreignId('docente_id')
                ->nullable() 
                ->constrained('docente')
                ->restrictOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['materia_id', 'paralelo_id', 'docente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paralelo_materia');
    }
};
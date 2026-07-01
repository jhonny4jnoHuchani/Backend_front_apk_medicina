<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_lugar', 150);
            $table->enum('tipo', ['aula', 'laboratorio', 'auditorio', 'exterior']);
            $table->string('edificio_campus', 100)->nullable();
            $table->jsonb('coordenadas')->nullable()
                ->comment('Array de puntos geométricos que definen el área válida');
            $table->smallInteger('tolerancia_metros')->default(50)
                ->comment('Radio en metros permitido alrededor de las coordenadas');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo'); // soft delete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion');
    }
};

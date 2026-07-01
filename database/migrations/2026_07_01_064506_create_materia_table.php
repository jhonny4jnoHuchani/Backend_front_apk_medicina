<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_materia', 200);
            $table->string('codigo', 30)->unique();
            $table->smallInteger('creditos')->default(0);
            $table->smallInteger('nivel')->default(1);
            $table->enum('modalidad', ['semestral', 'anual'])->default('semestral');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo'); // soft delete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};

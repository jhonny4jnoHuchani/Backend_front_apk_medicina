<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paralelos', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('grado');
            $table->string('paralelo', 10);
            $table->smallInteger('capacidad')->default(30);
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paralelos');
    }
};
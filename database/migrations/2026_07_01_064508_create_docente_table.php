<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('departamento', 150);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo'); // soft delete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};

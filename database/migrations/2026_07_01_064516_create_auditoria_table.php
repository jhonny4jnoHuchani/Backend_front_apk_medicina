<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('accion', 100);
            $table->string('entidad', 100);
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->jsonb('datos')->nullable();
            $table->timestamps();

            $table->index('usuario_id', 'idx_auditoria_usuario');
            $table->index('created_at', 'idx_auditoria_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};

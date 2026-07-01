<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permanencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_marcados')
                ->constrained('marcados')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permanencia');
    }
};

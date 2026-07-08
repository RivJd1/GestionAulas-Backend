<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Salones físicos. 'tipo' es texto libre e informativo:
     * no restringe qué secciones pueden usar el aula.
     */
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('edificio', 100);
            $table->string('piso', 20);
            $table->string('tipo', 100)->nullable();
            $table->unsignedInteger('capacidad_maxima')->default(0);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['disponible', 'mantenimiento'])->default('disponible');
            $table->timestamps();

            $table->unique(['nombre', 'edificio'], 'uq_aulas_nombre_edificio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};

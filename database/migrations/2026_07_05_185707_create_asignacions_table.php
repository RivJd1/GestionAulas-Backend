<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla puente: conecta una sección (catálogo) con un periodo
     * específico, el aula, el docente real y los estudiantes
     * matriculados ese semestre. Esto es lo que cambia cada periodo.
     *
     * Las validaciones de negocio (conflicto de aula, conflicto de
     * docente, sobrecupo) viven en el backend, no aquí. Esta tabla
     * solo garantiza que una misma sección no tenga dos asignaciones
     * dentro del mismo periodo.
     */
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_seccion')
                ->constrained('secciones')
                ->restrictOnDelete();
            $table->foreignId('id_periodo')
                ->constrained('periodos_academicos')
                ->restrictOnDelete();
            $table->foreignId('id_aula')
                ->nullable()
                ->constrained('aulas')
                ->nullOnDelete();
            $table->foreignId('id_docente')
                ->nullable()
                ->constrained('docentes')
                ->nullOnDelete();
            $table->unsignedInteger('estudiantes_matriculados')->default(0);
            $table->boolean('sobrecargo_confirmado')->default(false);
            $table->enum('estado', ['activa', 'asignada'])->default('activa');
            $table->timestamps();

            $table->unique(['id_seccion', 'id_periodo'], 'uq_asignaciones_seccion_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo permanente de materias/secciones. Se crea una sola
     * vez y se reutiliza en todos los periodos futuros a través
     * de la tabla `asignaciones` (no se duplica cada semestre).
     */
    public function up(): void
    {
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('materia', 150);
            $table->string('codigo_materia', 30)->nullable();
            $table->foreignId('id_docente')->nullable()->constrained('docentes')->nullOnDelete();
            $table->enum('tipo_sesion', ['matutino', 'vespertino']);
            $table->string('area_academica', 100);
            $table->decimal('duracion_sesion_horas', 4, 2);
            $table->decimal('horas_semanales_totales', 4, 2);
            $table->unsignedInteger('sesiones_por_semana')->default(1);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secciones');
    }
};

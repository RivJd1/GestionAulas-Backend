<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    use HasFactory;

    // Laravel pluralizaría "seccion" como "seccions" (regla en inglés),
    // por eso se fija la tabla explícitamente.
    protected $table = 'secciones';

    protected $fillable = [
        'materia',
        'codigo_materia',
        'id_docente',
        // Turno (matutino/vespertino), no tipo de clase.
        'tipo_sesion',
        'area_academica',
        'duracion_sesion_horas',
        'horas_semanales_totales',
        'sesiones_por_semana',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'duracion_sesion_horas' => 'decimal:2',
        'horas_semanales_totales' => 'decimal:2',
    ];

    /**
     * Docente titular por defecto de esta sección (opcional: una
     * sección puede quedar sin titular asignado por ahora).
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    /**
     * Todas las veces (periodos) en que esta sección se ha impartido.
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_seccion');
    }
}

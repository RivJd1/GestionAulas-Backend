<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seccion extends Model
{
    use HasFactory;

    protected $table = 'secciones';

    protected $fillable = [
        'materia',
        'codigo_materia',
        'id_docente',
        'tipo_sesion',
        'area_academica',
        'duracion_sesion_horas',
        'horas_semanales_totales',
        'cantidad_alumnos',
        'sesiones_por_semana',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'cantidad_alumnos' => 'integer',
        'duracion_sesion_horas' => 'decimal:2',
        'horas_semanales_totales' => 'decimal:2',
    ];


    protected $appends = ['docente_nombre'];

    public function getDocenteNombreAttribute(): ?string
    {
        return $this->docente?->nombre_completo;
    }


    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }


    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_seccion');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignacion extends Model
{
    use HasFactory;

    // Laravel pluralizaría "asignacion" como "asignacions" (regla en
    // inglés), por eso se fija la tabla explícitamente.
    protected $table = 'asignaciones';

    protected $fillable = [
        'id_seccion',
        'id_periodo',
        'id_aula',
        'id_docente',
        'estudiantes_matriculados',
        'sobrecargo_confirmado',
        'estado',
    ];

    protected $casts = [
        'id_seccion' => 'integer',
        'id_periodo' => 'integer',
        'id_aula' => 'integer',
        'id_docente' => 'integer',
        'estudiantes_matriculados' => 'integer',
        'sobrecargo_confirmado' => 'boolean',
    ];

    /**
     * Garantiza que las llaves foráneas planas (id_seccion, id_periodo,
     * id_aula, id_docente) SIEMPRE viajen en el JSON de salida, sin
     * importar qué relaciones se hayan cargado con ->load(). El
     * frontend depende de estos campos para saber en qué aula quedó
     * cada asignación después de moverla.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['id_seccion'] = $this->id_seccion;
        $data['id_periodo'] = $this->id_periodo;
        $data['id_aula'] = $this->id_aula;
        $data['id_docente'] = $this->id_docente;

        return $data;
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'id_seccion');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'id_periodo');
    }

    /**
     * Aula asignada (opcional: puede quedar pendiente de asignar).
     */
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    /**
     * Docente real que dicta esta asignación (puede diferir del
     * titular por defecto de la sección). Opcional: puede quedar
     * pendiente de asignar.
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    /**
     * Bloques de horario (día/hora) generados para esta asignación.
     */
    public function sesionesHorario(): HasMany
    {
        return $this->hasMany(SesionHorario::class, 'id_asignacion');
    }
}

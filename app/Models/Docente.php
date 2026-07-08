<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docentes';

    protected $fillable = [
        'nombre_completo',
        'correo_institucional',
        'telefono',
        'departamento',
        'especialidad',
        'estado',
    ];

    /**
     * Secciones de las que este docente es titular por defecto.
     */
    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'id_docente');
    }

    /**
     * Asignaciones concretas donde este docente dicta la clase
     * (puede diferir del titular de la sección en un periodo dado).
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_docente');
    }
}

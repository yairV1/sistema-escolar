<?php

namespace App\Modules\Calendario\Models;

use App\Modules\Auth\Models\Usuario;
use App\Modules\GestionAcademica\Models\AsignacionAcademica;
use App\Modules\GestionAcademica\Models\Curso;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Fila real de un evento del calendario (institucional/personal/etc), gestionada vía EventoService. */
class Evento extends Model
{
    protected $table = 'eventos';

    protected $primaryKey = 'id_evento';

    public const TIPOS_RECURRENCIA = ['ninguna', 'diaria', 'semanal', 'mensual'];

    protected $fillable = [
        'id_categoria',
        'id_usuario_creador',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'hora_inicio',
        'fecha_fin',
        'hora_fin',
        'todo_el_dia',
        'color_override',
        'prioridad',
        'estado',
        'visibilidad',
        'id_curso',
        'id_asignacion',
        'salon',
        'ubicacion',
        'tipo_recurrencia',
        'intervalo_recurrencia',
        'dias_semana_recurrencia',
        'fecha_fin_recurrencia',
        'recordatorio_minutos_antes',
        'estado_activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_fin_recurrencia' => 'date',
        'todo_el_dia' => 'boolean',
        'dias_semana_recurrencia' => 'array',
        'recordatorio_minutos_antes' => 'array',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(EventoCategoria::class, 'id_categoria', 'id_categoria');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creador', 'id_usuario');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(EventoHistorial::class, 'id_evento', 'id_evento')->latest('created_at');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(EventoParticipante::class, 'id_evento', 'id_evento');
    }

    public function excepciones(): HasMany
    {
        return $this->hasMany(EventoExcepcion::class, 'id_evento', 'id_evento');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(EventoAdjunto::class, 'id_evento', 'id_evento');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(EventoComentario::class, 'id_evento', 'id_evento')->latest('created_at');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_activo', 'activo');
    }

    public function scopeEnRango(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->where('fecha_inicio', '<=', $hasta)->where('fecha_fin', '>=', $desde);
    }

    /**
     * A qué canales de Reverb transmitir un cambio de este evento (Fase 4).
     * 'privado' solo llega al canal del propio creador; 'compartido' además
     * a cada participante; 'publico' a su curso (o al canal institucional
     * si no tiene curso) — nunca se transmite un evento privado fuera del
     * canal de su dueño.
     */
    public function canalesBroadcast(): array
    {
        $canales = [new PrivateChannel('App.Modules.Auth.Models.Usuario.'.$this->id_usuario_creador)];

        if ($this->visibilidad === 'compartido') {
            foreach ($this->participantes as $participante) {
                if ($participante->id_usuario !== $this->id_usuario_creador) {
                    $canales[] = new PrivateChannel('App.Modules.Auth.Models.Usuario.'.$participante->id_usuario);
                }
            }
        } elseif ($this->visibilidad === 'publico') {
            $canales[] = $this->id_curso
                ? new PrivateChannel('calendario.curso.'.$this->id_curso)
                : new PrivateChannel('calendario.institucional');
        }

        return $canales;
    }
}

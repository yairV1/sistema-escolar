<?php

namespace App\Modules\Calendario\Requests;

use App\Modules\Calendario\Models\Evento;
use App\Modules\Calendario\Models\EventoCategoria;
use App\Modules\GestionAcademica\Models\Horario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Un solo Request para crear y editar (igual convención que MateriaRequest/
 * HorarioRequest/CursoRequest en GestionAcademica: mismas reglas para
 * ambas acciones, reemplazo completo — no PATCH parcial).
 */
class EventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $evento = $this->route('evento');

        if ($evento) {
            if (! $this->user()->can('update', $evento)) {
                return false;
            }

            // Cambiar de categoría al editar exige el mismo permiso de 'create'
            // que pedirla de entrada — si no, un dueño podría mover su evento
            // a una categoría donde nunca tuvo permiso de crear.
            $nuevaCategoriaId = (int) $this->input('id_categoria');
            if ($nuevaCategoriaId && $nuevaCategoriaId !== $evento->id_categoria) {
                $nuevaCategoria = EventoCategoria::find($nuevaCategoriaId);

                return $nuevaCategoria ? $this->user()->can('create', [Evento::class, $nuevaCategoria]) : true;
            }

            return true;
        }

        $categoria = EventoCategoria::find($this->input('id_categoria'));
        if (! $categoria) {
            return true; // el 422 de 'exists' abajo es el error correcto, no un 403 engañoso.
        }

        return $this->user()->can('create', [Evento::class, $categoria]);
    }

    public function rules(): array
    {
        return [
            'id_categoria' => ['required', 'integer', 'exists:evento_categorias,id_categoria'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'hora_inicio' => ['nullable', 'date_format:H:i', 'required_if:todo_el_dia,0'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'required_if:todo_el_dia,0', 'after:hora_inicio'],
            'todo_el_dia' => ['nullable', 'boolean'],
            'color_override' => ['nullable', 'string', 'max:30'],
            'prioridad' => ['required', 'in:baja,media,alta,urgente'],
            'visibilidad' => ['required', 'in:privado,publico,compartido'],
            'participantes' => ['nullable', 'array', 'required_if:visibilidad,compartido'],
            'participantes.*' => ['integer', Rule::exists('usuarios', 'id_usuario')->whereIn('id_rol', [1, 2, 3, 4, 5])],
            'id_curso' => ['nullable', 'integer', 'exists:cursos,id_curso'],
            'id_asignacion' => ['nullable', 'integer', 'exists:asignaciones_academicas,id_asignacion'],
            'salon' => ['nullable', 'string', 'max:60'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'tipo_recurrencia' => ['required', 'in:'.implode(',', Evento::TIPOS_RECURRENCIA)],
            'intervalo_recurrencia' => ['nullable', 'integer', 'min:1', 'max:52'],
            'dias_semana_recurrencia' => ['nullable', 'array'],
            'dias_semana_recurrencia.*' => ['in:'.implode(',', Horario::DIAS_SEMANA)],
            'fecha_fin_recurrencia' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'recordatorio_minutos_antes' => ['nullable', 'array'],
            'recordatorio_minutos_antes.*' => ['integer', 'in:'.implode(',', array_keys(config('calendario.recordatorio_opciones_minutos')))],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'id_categoria.exists' => 'La categoría seleccionada no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'hora_inicio.required_if' => 'La hora de inicio es obligatoria si el evento no dura todo el día.',
            'hora_fin.required_if' => 'La hora de fin es obligatoria si el evento no dura todo el día.',
            'participantes.required_if' => 'Selecciona al menos una persona para compartir el evento.',
            'participantes.*.exists' => 'Uno de los participantes seleccionados no es válido.',
            'fecha_fin_recurrencia.after_or_equal' => 'La fecha de fin de la recurrencia debe ser igual o posterior a la fecha de inicio del evento.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $datos = parent::validated($key, $default);
        $datos['todo_el_dia'] = (bool) ($datos['todo_el_dia'] ?? false);

        if ($datos['todo_el_dia']) {
            $datos['hora_inicio'] = null;
            $datos['hora_fin'] = null;
        }

        if (($datos['tipo_recurrencia'] ?? 'ninguna') === 'ninguna') {
            $datos['intervalo_recurrencia'] = null;
            $datos['dias_semana_recurrencia'] = null;
            $datos['fecha_fin_recurrencia'] = null;
        } else {
            $datos['intervalo_recurrencia'] = $datos['intervalo_recurrencia'] ?? 1;
        }

        return $datos;
    }
}

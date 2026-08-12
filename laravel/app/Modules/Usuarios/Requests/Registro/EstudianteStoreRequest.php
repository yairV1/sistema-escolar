<?php

namespace App\Modules\Usuarios\Requests\Registro;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class EstudianteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** El formulario deja escribir la fecha de nacimiento como texto DD/MM/AAAA (más rápido que navegar el calendario nativo hasta años lejanos); acá se normaliza a Y-m-d para que la regla 'date' y el guardado en BD sigan iguales. Si no matchea el formato se deja tal cual y 'date' la rechaza con el mensaje estándar. */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('fecha_nacimiento')) {
            return;
        }

        try {
            $fecha = Carbon::createFromFormat('d/m/Y', trim($this->input('fecha_nacimiento')));
            $this->merge(['fecha_nacimiento' => $fecha->format('Y-m-d')]);
        } catch (\Throwable) {
            // Deja el valor original; la regla 'date' de abajo lo rechaza.
        }
    }

    public function rules(): array
    {
        return [
            'id_solicitud' => ['nullable', 'integer', 'exists:solicitudes_admision,id_solicitud'],

            // Paso 1 — Datos personales
            'primer_nombre' => ['required', 'string', 'max:100'],
            'segundo_nombre' => ['nullable', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:TI,RC,CC,CE'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'fecha_nacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F,Otro'],
            'lugar_nacimiento' => ['nullable', 'string', 'max:100'],
            'nacionalidad' => ['nullable', 'string', 'max:100'],
            'grupo_sanguineo' => ['nullable', 'string', 'max:5'],
            'eps' => ['nullable', 'string', 'max:100'],
            'condicion_medica' => ['nullable', 'string'],

            // Paso 2 — Contacto
            'direccion' => ['required', 'string', 'max:200'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'localidad' => ['required', 'string', 'max:100'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estrato' => ['nullable', 'integer', 'between:1,6'],
            'telefono_estudiante' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
            'correo_estudiante' => ['nullable', 'email'],

            // Paso 3 — Académico
            'tipo_matricula' => ['required', 'in:nueva,reingreso,traslado,interno'],
            'anio_lectivo' => ['required', 'integer'],
            'grado' => ['required', 'in:PRE,1,2,3,4,5,6,7,8,9,10,11'],
            'grupo' => ['nullable', 'in:A,B,C,D'],
            'jornada' => ['required', 'in:manana,tarde,noche,unica'],
            'colegio_anterior' => ['nullable', 'string', 'max:150'],
            'nee' => ['required', 'in:si,no'],
            'nee_descripcion' => ['required_if:nee,si', 'nullable', 'string'],

            // Paso 4 — Acudiente
            'acudiente_nombres' => ['required', 'string', 'max:150'],
            'acudiente_parentesco' => ['required', 'in:madre,padre,abuelo,tio,hermano,acudiente,otro'],
            'acudiente_tipo_documento' => ['required', 'in:CC,CE,PAS'],
            'acudiente_numero_documento' => ['required', 'string', 'max:20'],
            'acudiente_telefono' => ['required', 'regex:/^[0-9+\s-]{7,20}$/'],
            'acudiente_telefono2' => ['nullable', 'regex:/^[0-9+\s-]{7,20}$/'],
            'acudiente_correo' => ['required', 'email'],
            'acudiente_ocupacion' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'fecha_nacimiento.date' => 'Escribe la fecha en formato DD/MM/AAAA.',
            'nee_descripcion.required_if' => 'Describe la necesidad educativa especial.',
            '*.regex' => 'El formato no es válido.',
            '*.email' => 'Ingresa un correo válido.',
        ];
    }
}

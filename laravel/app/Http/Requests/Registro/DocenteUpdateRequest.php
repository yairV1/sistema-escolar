<?php

namespace App\Http\Requests\Registro;

class DocenteUpdateRequest extends DocenteStoreRequest
{
    // Mismas reglas que el registro: a diferencia de Estudiante, Docente no
    // tiene un bloque de campos opcionales que relajar en edición.
}

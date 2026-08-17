<?php

namespace App\Shared\Fixtures;

use Illuminate\Support\Collection;

/**
 * Datos de muestra para las vistas de Estudiante/Acudiente (UI-only, sin
 * conexión a BD todavía). Centraliza la forma de los datos que consumen
 * ambos paneles — incluido el partial horario-grid, que espera la misma
 * forma que el módulo de Gestión Académica (asignacion->materia,
 * asignacion->profesor->usuario) — para que conectarlos a datos reales
 * más adelante sea solo cambiar el origen, no la vista.
 */
class PortalFamiliaFixtures
{
    private const MATERIAS = [
        ['id' => 1, 'nombre' => 'Matemáticas', 'docente' => 'Laura Ramírez', 'icono' => 'fa-square-root-variable'],
        ['id' => 2, 'nombre' => 'Español',     'docente' => 'Carlos Peña',   'icono' => 'fa-book-open'],
        ['id' => 3, 'nombre' => 'Inglés',      'docente' => 'Ana Torres',    'icono' => 'fa-language'],
        ['id' => 4, 'nombre' => 'Educación Física', 'docente' => 'Diego Salas', 'icono' => 'fa-futbol'],
        ['id' => 5, 'nombre' => 'Química',     'docente' => 'Marta Gil',     'icono' => 'fa-flask'],
        ['id' => 6, 'nombre' => 'Religión',    'docente' => 'Pedro Ríos',    'icono' => 'fa-hands-praying'],
        ['id' => 7, 'nombre' => 'Ciencias Sociales', 'docente' => 'Julia Cortés', 'icono' => 'fa-earth-americas'],
        ['id' => 8, 'nombre' => 'Artística',   'docente' => 'Sara Molina',   'icono' => 'fa-palette'],
    ];

    private const HORARIO_SEMANA = [
        'lunes'     => [1, 2, 5, 3],
        'martes'    => [2, 4, 1, 7],
        'miercoles' => [5, 3, 6, 1],
        'jueves'    => [7, 8, 2, 4],
        'viernes'   => [3, 1, 8, 4],
    ];

    private const HIJOS = [
        1 => ['id' => 1, 'nombres' => 'Sofía', 'apellidos' => 'Martínez Gómez', 'curso' => '6°A', 'codigo' => 'EST-2026-014', 'perfil' => 'ordenado'],
        2 => ['id' => 2, 'nombres' => 'Mateo', 'apellidos' => 'Martínez Gómez', 'curso' => '9°B', 'codigo' => 'EST-2026-071', 'perfil' => 'con_alertas'],
    ];

    private const ACUDIENTE = [
        'nombre' => 'Claudia Gómez de Martínez',
        'parentesco' => 'Madre',
        'telefono' => '300 555 1234',
        'correo' => 'claudia.gomez@example.com',
    ];

    /** Hijos de muestra de un acudiente (Acudiente/*, ver docblock de AcudienteController). */
    public static function hijos(): Collection
    {
        return collect(self::HIJOS)->map(fn ($h) => (object) $h);
    }

    public static function hijoActivo(int $id): object
    {
        return (object) (self::HIJOS[$id] ?? self::HIJOS[1]);
    }

    /** Acudiente de muestra asociado a un estudiante (tab "Mi familia" del perfil). */
    public static function acudienteDe(): object
    {
        return (object) self::ACUDIENTE;
    }

    public static function materias(): Collection
    {
        return collect(self::MATERIAS)->map(fn ($m) => (object) $m);
    }

    /** Grilla semanal en la misma forma que espera horario-grid.blade.php. */
    public static function horario(string $curso = '6°A'): Collection
    {
        $materias = self::materias()->keyBy('id');
        $idHorario = 1;
        $horas = ['07:00', '08:00', '09:10', '11:00'];
        $bloques = collect();

        foreach (self::HORARIO_SEMANA as $dia => $idsMateria) {
            foreach ($idsMateria as $i => $idMateria) {
                $materia = $materias->get($idMateria);
                $inicio = $horas[$i];
                $fin = date('H:i', strtotime($inicio.':00 +50 minutes'));

                $bloques->push((object) [
                    'id_horario' => $idHorario++,
                    'dia_semana' => $dia,
                    'hora_inicio' => $inicio.':00',
                    'hora_fin' => $fin.':00',
                    'salon' => 'Salón '.(200 + $idMateria),
                    'asignacion' => (object) [
                        'id_materia' => $materia->id,
                        'materia' => (object) ['nombre_materia' => $materia->nombre],
                        'curso' => (object) ['nombre_curso' => $curso],
                        'profesor' => (object) [
                            'usuario' => (object) array_combine(
                                ['nombres', 'apellidos'],
                                explode(' ', $materia->docente, 2)
                            ),
                        ],
                    ],
                ]);
            }
        }

        return $bloques;
    }

    /**
     * @return array<string, object> boletín por periodo, con detalle de notas.
     */
    public static function notasPorPeriodo(string $perfil = 'ordenado'): array
    {
        $base = ['Matemáticas' => 4.5, 'Español' => 4.2, 'Inglés' => 4.6, 'Educación Física' => 4.8, 'Química' => 4.0, 'Religión' => 4.7, 'Ciencias Sociales' => 4.3, 'Artística' => 4.9];

        if ($perfil === 'con_alertas') {
            $base['Matemáticas'] = 2.8;
            $base['Química'] = 3.1;
        }

        $periodos = [];
        foreach (['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4'] as $i => $nombrePeriodo) {
            $factor = 1 - ($i * 0.03);
            $detalle = self::materias()->map(function ($materia) use ($base, $factor) {
                $nota = round(($base[$materia->nombre] ?? 4.0) * $factor, 1);

                return (object) [
                    'materia' => $materia->nombre,
                    'profesor' => $materia->docente,
                    'nota_definitiva' => min(5.0, $nota),
                ];
            });

            $periodos[$nombrePeriodo] = (object) [
                'promedio_general' => round($detalle->avg('nota_definitiva'), 1),
                'puesto_curso' => $perfil === 'con_alertas' ? 22 : 4,
                'total_curso' => 30,
                'detalle' => $detalle,
            ];
        }

        return $periodos;
    }

    public static function asistencia(string $perfil = 'ordenado'): object
    {
        $filas = collect();
        $hoy = now();
        $ausentes = $perfil === 'con_alertas' ? [1, 4, 9] : [6];
        $tardes = $perfil === 'con_alertas' ? [2, 7] : [];

        for ($i = 12; $i >= 0; $i--) {
            $fecha = $hoy->copy()->subDays($i);
            if (in_array($fecha->dayOfWeek, [0, 6], true)) {
                continue;
            }

            $estado = 'presente';
            if (in_array($i, $ausentes, true)) {
                $estado = 'ausente';
            } elseif (in_array($i, $tardes, true)) {
                $estado = 'tarde';
            }

            $filas->push((object) ['fecha' => $fecha->toDateString(), 'estado' => $estado]);
        }

        $total = $filas->count();
        $presentes = $filas->where('estado', 'presente')->count();
        $porcentaje = $total ? round($presentes / $total * 100) : 100;

        return (object) [
            'porcentaje' => $porcentaje,
            'faltas' => $filas->where('estado', 'ausente')->count(),
            'tardanzas' => $filas->where('estado', 'tarde')->count(),
            'filas' => $filas->sortByDesc('fecha')->values(),
        ];
    }

    public static function comunicados(string $perfil = 'ordenado'): Collection
    {
        $items = [
            ['titulo' => 'Suspensión de clases el viernes', 'mensaje' => 'Por mantenimiento eléctrico programado, no habrá clases el viernes en la jornada de la mañana.', 'tipo' => 'informativa', 'dias' => 1, 'leido' => false],
            ['titulo' => 'Entrega de boletines — Periodo 3', 'mensaje' => 'Los boletines del tercer periodo estarán disponibles a partir del próximo lunes en este panel.', 'tipo' => 'academica', 'dias' => 3, 'leido' => false],
            ['titulo' => 'Jornada de puertas abiertas', 'mensaje' => 'Invitamos a toda la comunidad educativa a la jornada de puertas abiertas el sábado 9am.', 'tipo' => 'informativa', 'dias' => 6, 'leido' => true],
            ['titulo' => 'Recordatorio: útiles de laboratorio', 'mensaje' => 'Para la clase de Química de esta semana se requiere bata y gafas de protección.', 'tipo' => 'academica', 'dias' => 9, 'leido' => true],
        ];

        if ($perfil === 'con_alertas') {
            array_unshift($items, [
                'titulo' => 'Citación: seguimiento académico',
                'mensaje' => 'Se solicita la presencia del acudiente para revisar el desempeño académico del estudiante en Matemáticas y Química.',
                'tipo' => 'disciplinaria',
                'dias' => 0,
                'leido' => false,
            ]);
        }

        return collect($items)->map(fn ($item, $i) => (object) array_merge($item, [
            'id' => $i + 1,
            'fecha' => now()->subDays($item['dias'])->format('d/m/Y'),
        ]));
    }
}

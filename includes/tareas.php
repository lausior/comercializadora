<?php

/* =====================================================
   TAREAS DEL PLANIFICADOR
   =====================================================
   Validación común de guardar_tarea.php y
   actualizar_tarea.php (mismas reglas que js/planificador.js).
========================================================= */

const AREAS_TAREA = ['Tarifas', 'Clientes', 'Incidencias', 'Comparador', 'Sistema'];
const ESTADOS_TAREA = ['Pendiente', 'En curso', 'Completada'];


/**
 * Devuelve todos los errores de los datos de una tarea:
 * [['mensaje' => string, 'campo' => string], ...] (vacío si
 * son válidos), listo para establecerErroresFormulario().
 */
function validarDatosTarea(string $titulo, string $area, string $fecha, string $hora, string $responsable, string $estado): array
{
    $errores = [];

    if ($titulo === '') {
        $errores[] = ['mensaje' => 'Este campo es obligatorio.', 'campo' => 'titulo'];
    } elseif (mb_strlen($titulo, 'UTF-8') > 150) {
        $errores[] = ['mensaje' => 'No puede superar los 150 caracteres.', 'campo' => 'titulo'];
    }

    if (!in_array($area, AREAS_TAREA, true)) {
        $errores[] = ['mensaje' => 'Selecciona un área.', 'campo' => 'area'];
    }

    $fechaLeida = DateTime::createFromFormat('!Y-m-d', $fecha);

    if (!$fechaLeida || $fechaLeida->format('Y-m-d') !== $fecha) {
        $errores[] = ['mensaje' => 'Selecciona una fecha válida.', 'campo' => 'fecha'];
    }

    if ($hora !== '' && !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $hora)) {
        $errores[] = ['mensaje' => 'Introduce una hora válida.', 'campo' => 'hora'];
    }

    if (mb_strlen($responsable, 'UTF-8') > 100) {
        $errores[] = ['mensaje' => 'No puede superar los 100 caracteres.', 'campo' => 'responsable'];
    }

    if (!in_array($estado, ESTADOS_TAREA, true)) {
        $errores[] = ['mensaje' => 'Selecciona un estado.', 'campo' => 'estado'];
    }

    return $errores;
}

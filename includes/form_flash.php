<?php

/**
 * =====================================================
 * MENSAJES DE ERROR DE FORMULARIO (POST/REDIRECT/GET)
 * =====================================================
 *
 * Antes, los guardar_*.php / actualizar_*.php cortaban la
 * ejecución con die() ante un dato inválido, dejando al
 * usuario en una página en blanco con el mensaje suelto.
 *
 * Con este patrón, en su lugar: se guarda el mensaje y los
 * datos ya escritos en sesión, y se redirige de vuelta al
 * formulario (crear_x.php / editar_x.php), que los recupera
 * con obtenerErrorFormulario() y:
 *   - muestra en su #form-error-general el aviso general
 *     (MENSAJE_FORMULARIO_CON_ERRORES) más los errores que no
 *     son de un campo concreto (p. ej. "No se ha podido
 *     guardar...").
 *   - rellena los campos con lo que la persona ya había
 *     escrito, en vez de dejarlos en blanco (o, en edición,
 *     en vez de volver a mostrar los valores originales de
 *     la base de datos).
 *   - resalta TODOS los campos que han fallado igual que la
 *     validación JS: clase "input-error" en el campo y su
 *     mensaje en el span#error-<campo>.
 *
 * Requiere sesión ya iniciada (todas las páginas que la
 * usan llaman session_start() antes de permisos.php).
 *
 * =====================================================
 */

// Mismo texto que usan los JS de los formularios al enviar
// con errores.
const MENSAJE_FORMULARIO_CON_ERRORES = 'El formulario contiene errores. Revísalos antes de enviarlo.';

/**
 * Guarda los errores y vuelve al formulario.
 *
 * @param array $errores [['mensaje' => string, 'campo' => ?string], ...]
 *                       Si un campo tiene varios, se muestra el primero.
 */
function establecerErroresFormulario(array $errores, array $datos, string $redirigirA): void
{
    $campos = [];
    $generales = [];

    foreach ($errores as $error) {

        $campo = $error['campo'] ?? null;

        if ($campo === null) {
            $generales[] = $error['mensaje'];
        } elseif (!isset($campos[$campo])) {
            $campos[$campo] = $error['mensaje'];
        }

    }

    if (!empty($campos)) {
        array_unshift($generales, MENSAJE_FORMULARIO_CON_ERRORES);
    }

    $_SESSION['form_error'] = implode(' ', array_unique($generales));
    $_SESSION['form_datos'] = $datos;
    $_SESSION['form_campos'] = $campos;

    header('Location: ' . $redirigirA);
    exit;
}

/**
 * Atajo para un único error (de un campo, o general si
 * $campo es null).
 */
function establecerErrorFormulario(string $mensaje, array $datos, string $redirigirA, ?string $campo = null): void
{
    establecerErroresFormulario([['mensaje' => $mensaje, 'campo' => $campo]], $datos, $redirigirA);
}

/**
 * Devuelve ['mensaje' => string (aviso general), 'datos' => array,
 * 'campos' => [campo => mensaje]] si venimos de un
 * establecerErroresFormulario(), o null si no hay error
 * pendiente. Consume el flash: una vez leído, desaparece.
 */
function obtenerErrorFormulario(): ?array
{
    if (!isset($_SESSION['form_error'])) {
        return null;
    }

    $error = [
        'mensaje' => $_SESSION['form_error'],
        'datos'   => $_SESSION['form_datos'] ?? [],
        'campos'  => $_SESSION['form_campos'] ?? [],
    ];

    unset($_SESSION['form_error'], $_SESSION['form_datos'], $_SESSION['form_campos']);

    return $error;
}

/**
 * Clase CSS a añadir al <input>/<select> de $campo si ha
 * fallado en el envío anterior (mismo criterio visual que la
 * validación JS: clase "input-error").
 */
function claseErrorCampo(?array $errorFormulario, string $campo): string
{
    return isset($errorFormulario['campos'][$campo]) ? 'input-error' : '';
}

/**
 * Mensaje a mostrar en el span#error-<campo> de $campo si ha
 * fallado en el envío anterior. Ya escapado.
 */
function mensajeErrorCampo(?array $errorFormulario, string $campo): string
{
    return htmlspecialchars($errorFormulario['campos'][$campo] ?? '', ENT_QUOTES, 'UTF-8');
}


/**
 * Valor a mostrar en un <input>/<textarea> tras volver de un
 * error: lo que la persona ya había escrito ($datosPrevios,
 * viene de $_POST) si lo hay, si no el valor por defecto
 * (normalmente el dato ya guardado, en un formulario de
 * edición). Ya escapado para HTML.
 */
function valorFormulario(array $datosPrevios, string $campo, string $porDefecto = ''): string
{
    $valor = array_key_exists($campo, $datosPrevios)
        ? (string) $datosPrevios[$campo]
        : $porDefecto;

    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

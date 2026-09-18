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
 *   - muestra el mensaje en su #form-error-general
 *   - rellena los campos con lo que la persona ya había
 *     escrito, en vez de dejarlos en blanco (o, en edición,
 *     en vez de volver a mostrar los valores originales de
 *     la base de datos).
 *   - si el error viene de un campo concreto (se pasó
 *     $campo), resalta ese campo igual que la validación
 *     JS: le añade la clase "input-error" y rellena su
 *     span#error-<campo>.
 *
 * Requiere sesión ya iniciada (todas las páginas que la
 * usan llaman session_start() antes de permisos.php).
 *
 * =====================================================
 */

function establecerErrorFormulario(string $mensaje, array $datos, string $redirigirA, ?string $campo = null): void
{
    $_SESSION['form_error'] = $mensaje;
    $_SESSION['form_datos'] = $datos;
    $_SESSION['form_campo'] = $campo;

    header('Location: ' . $redirigirA);
    exit;
}

/**
 * Devuelve ['mensaje' => string, 'datos' => array, 'campo' => ?string]
 * si venimos de un establecerErrorFormulario(), o null si no hay
 * error pendiente. Consume el flash: una vez leído, desaparece.
 */
function obtenerErrorFormulario(): ?array
{
    if (!isset($_SESSION['form_error'])) {
        return null;
    }

    $error = [
        'mensaje' => $_SESSION['form_error'],
        'datos'   => $_SESSION['form_datos'] ?? [],
        'campo'   => $_SESSION['form_campo'] ?? null,
    ];

    unset($_SESSION['form_error'], $_SESSION['form_datos'], $_SESSION['form_campo']);

    return $error;
}

/**
 * Clase CSS a añadir al <input>/<select> de $campo si es el
 * que ha fallado en el envío anterior (mismo criterio visual
 * que la validación JS: clase "input-error").
 */
function claseErrorCampo(?array $errorFormulario, string $campo): string
{
    return ($errorFormulario['campo'] ?? null) === $campo ? 'input-error' : '';
}

/**
 * Mensaje a mostrar en el span#error-<campo> de $campo si es
 * el que ha fallado en el envío anterior. Ya escapado.
 */
function mensajeErrorCampo(?array $errorFormulario, string $campo): string
{
    if (($errorFormulario['campo'] ?? null) !== $campo) {
        return '';
    }

    return htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8');
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

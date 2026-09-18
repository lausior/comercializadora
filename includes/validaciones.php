<?php

/**
 * =====================================================
 * FUNCIONES DE VALIDACIÓN
 * =====================================================
 *
 * Funciones centralizadas para validar los datos
 * introducidos en los formularios de la aplicación.
 *
 */

function validarCaracteresNombre(string $texto): bool
{
    $texto = trim($texto);

    return preg_match(
        "/^[\p{L}]+(?:[ '\-][\p{L}]+)*$/u",
        $texto
    ) === 1;
}


/**
 * =====================================================
 * VALIDAR NOMBRE
 * =====================================================
 *
 * Permite:
 * - Letras
 * - Tildes
 * - Ñ
 * - Espacios
 * - Guiones
 * - Apóstrofes
 *
 * Ejemplos válidos:
 *   Laura
 *   José
 *   María José
 *   Ana-María
 *   O'Connor
 *
 */
function validarNombre(string $nombre): bool
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return false;
    }

    if (mb_strlen($nombre, 'UTF-8') < 2) {
        return false;
    }

    if (mb_strlen($nombre, 'UTF-8') > 50) {
        return false;
    }

    return validarCaracteresNombre($nombre);
}


/**
 * =====================================================
 * VALIDAR CARACTERES DE NOMBRE DE EMPRESA
 * =====================================================
 *
 * Igual que validarCaracteresNombre(), pero además permite
 * puntos, para razones sociales como "Sociedad S.L." o
 * "Comercializadora S.A.".
 *
 */
function validarCaracteresNombreEmpresa(string $texto): bool
{
    $texto = trim($texto);

    return preg_match(
        "/^[\p{L}0-9]+(?:[ '\-.][\p{L}0-9]+)*\.?$/u",
        $texto
    ) === 1;
}


/**
 * =====================================================
 * VALIDAR NOMBRE DE EMPRESA
 * =====================================================
 * Igual que validarNombre(), pero para razones sociales:
 * permite también números y puntos, y admite nombres más
 * largos (hasta 150 caracteres, como la columna de la BD).
 *
 * Ejemplos válidos:
 *   Sociedad S.L.
 *   Comercializadora Eléctrica S.A.
 *   3M España
 */
function validarNombreEmpresa(string $nombre): bool
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return false;
    }

    if (mb_strlen($nombre, 'UTF-8') < 2) {
        return false;
    }

    if (mb_strlen($nombre, 'UTF-8') > 150) {
        return false;
    }

    return validarCaracteresNombreEmpresa($nombre);
}


/**
 * =====================================================
 * VALIDAR APELLIDOS
 * =====================================================
 * Permite:
 * - Letras
 * - Tildes
 * - Ñ
 * - Espacios
 * - Guiones
 * - Apóstrofes
 */
function validarApellidos(string $apellidos): bool
{
    $apellidos = trim($apellidos);

    if ($apellidos === '') {
        return false;
    }

    if (mb_strlen($apellidos, 'UTF-8') < 2) {
        return false;
    }

    if (mb_strlen($apellidos, 'UTF-8') > 100) {
        return false;
    }

    return validarCaracteresNombre($apellidos);
}


/**
 * =====================================================
 * VALIDAR DNI
 * =====================================================
 *
 * Comprueba:
 * - 8 números
 * - 1 letra
 * - Letra de control correcta
 */
function validarDNI(string $dni): bool
{
    $dni = strtoupper(trim($dni));

    if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
        return false;
    }

    $numero = substr($dni, 0, 8);
    $letra = substr($dni, -1);

    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';

    $letraCorrecta = $letras[(int) $numero % 23];

    return $letra === $letraCorrecta;
}


/**
 * =====================================================
 * VALIDAR NIE
 * =====================================================
 * Comprueba:
 * - X, Y o Z
 * - 7 números
 * - Letra de control correcta
 */
function validarNIE(string $nie): bool
{
    $nie = strtoupper(trim($nie));

    if (!preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $nie)) {
        return false;
    }

    $primerCaracter = $nie[0];

    switch ($primerCaracter) {

        case 'X':
            $numero = '0' . substr($nie, 1, 7);
            break;

        case 'Y':
            $numero = '1' . substr($nie, 1, 7);
            break;

        case 'Z':
            $numero = '2' . substr($nie, 1, 7);
            break;

        default:
            return false;
    }

    $letra = substr($nie, -1);

    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';

    $letraCorrecta = $letras[(int) $numero % 23];

    return $letra === $letraCorrecta;
}


/**
 * =====================================================
 * VALIDAR DNI O NIE
 * =====================================================
 */
function validarDniNie(string $documento): bool
{
    $documento = strtoupper(trim($documento));

    return validarDNI($documento) || validarNIE($documento);
}


/**
 * =====================================================
 * VALIDAR CIF
 * =====================================================
 * Comprueba:
 * - Formato del CIF
 * - Dígito/letra de control

 */
function validarCIF(string $cif): bool
{
    $cif = strtoupper(trim($cif));

    if (!preg_match('/^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/', $cif)) {
        return false;
    }

    $letraInicial = $cif[0];

    $numeros = substr($cif, 1, 7);
    $control = substr($cif, -1);

    $suma = 0;

    for ($i = 0; $i < 7; $i++) {

        $numero = (int) $numeros[$i];

        if ($i % 2 === 0) {

            $resultado = $numero * 2;

            if ($resultado >= 10) {
                $resultado = intdiv($resultado, 10)
                    + ($resultado % 10);
            }

            $suma += $resultado;

        } else {

            $suma += $numero;

        }
    }

    $digitoControl = (10 - ($suma % 10)) % 10;

    /*
     * Algunos CIF utilizan número como control y otros utilizan letra.
     */

    $letrasControl = 'JABCDEFGHI';

    $controlNumerico = (string) $digitoControl;
    $controlAlfabetico = $letrasControl[$digitoControl];

    if (in_array($letraInicial, ['A', 'B', 'E', 'H'], true)) {

        return $control === $controlNumerico;

    }

    if (in_array($letraInicial, ['K', 'P', 'Q', 'S'], true)) {

        return $control === $controlAlfabetico;

    }

    return $control === $controlNumerico
        || $control === $controlAlfabetico;
}


/**
 * =====================================================
 * VALIDAR DIRECCIÓN
 * =====================================================
 * La dirección debe permitir números y caracteres
 * habituales de una dirección.
 */
function validarDireccion(string $direccion): bool
{
    $direccion = trim($direccion);

    if ($direccion === '') {
        return false;
    }

    if (mb_strlen($direccion, 'UTF-8') < 3) {
        return false;
    }

    if (mb_strlen($direccion, 'UTF-8') > 150) {
        return false;
    }

    /*
     * Permitimos letras, números y puntuación habitual
     * de las direcciones.
     */
    return preg_match(
        "/^[\p{L}\p{N}\s.,'ºª°\/\-]+$/u",
        $direccion
    ) === 1;
}


/**
 * =====================================================
 * VALIDAR EMAIL
 * =====================================================
 *
 */
function validarEmail(string $email): bool
{
    $email = trim($email);

    if ($email === '') {
        return false;
    }

    if (strlen($email) > 150) {
        return false;
    }

    return filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) !== false;
}


/**
 * =====================================================
 * VALIDAR TELÉFONO
 * =====================================================
 *
 * Admite teléfonos nacionales e internacionales:
 * - Un "+" inicial opcional (prefijo internacional)
 * - Números, espacios, guiones y paréntesis
 * - Entre 7 y 15 dígitos en total (sin contar separadores)
 *
 * Ejemplos:
 *   612345678
 *   912 345 678
 *   +34 612 345 678
 *   +1 (555) 123-4567
 *
 */
function validarTelefono(string $telefono): bool
{
    $telefono = trim($telefono);

    if ($telefono === '') {
        return false;
    }

    if (preg_match('/^\+?[0-9\s\-()]+$/', $telefono) !== 1) {
        return false;
    }

    $digitos = preg_replace('/\D/', '', $telefono);

    return strlen($digitos) >= 7 && strlen($digitos) <= 15;
}


/**
 * =====================================================
 * VALIDAR USERNAME
 * =====================================================
 *
 * El username solo permite texto:
 * - Letras
 * - Sin números
 * - Sin caracteres especiales
 * - Sin espacios
 *
 * Ejemplos válidos:
 *   laura
 *   juan
 *   maria
 *
 */
function validarUsername(string $username): bool
{
    $username = trim($username);

    if ($username === '') {
        return false;
    }

    if (mb_strlen($username, 'UTF-8') < 2) {
        return false;
    }

    if (mb_strlen($username, 'UTF-8') > 30) {
        return false;
    }

    return preg_match('/^[a-z]+$/', $username) === 1;
}


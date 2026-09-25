<?php

// loginAcceso() y PASSWORD_INICIAL (usuario de acceso de la
// empresa).
require_once __DIR__ . '/accesos.php';


/* =====================================================
   LOGO DE EMPRESA
========================================================= */


/**
 * Devuelve la ruta relativa (desde la raíz del proyecto) al
 * logo de la empresa, o null si no tiene ninguno subido.
 */
function obtenerLogoEmpresa(PDO $pdo, int $idEmpresa): ?string
{
    $stmt = $pdo->prepare("SELECT logo FROM empresas WHERE id = ?");
    $stmt->execute([$idEmpresa]);

    $logo = $stmt->fetchColumn();

    return $logo !== false && $logo !== null ? $logo : null;
}


/**
 * Guarda la ruta del logo de la empresa.
 */
function guardarLogoEmpresa(PDO $pdo, int $idEmpresa, ?string $rutaRelativa): void
{
    $stmt = $pdo->prepare("UPDATE empresas SET logo = ? WHERE id = ?");
    $stmt->execute([$rutaRelativa, $idEmpresa]);
}


/* =====================================================
   MOTIVOS DE INACTIVO
   =====================================================
   Valor que se guarda en motivo_inactivo => texto que se
   ve. Son la única lista: de aquí salen los desplegables
   (pintarOpcionesMotivo()), las validaciones (array_keys) y
   las etiquetas (etiquetaMotivoInactivo()).
   Las mismas listas están también en js/usuarios.js, que
   reconstruye el desplegable al cambiar el rol.
========================================================= */

// Empresas y su usuario de acceso (rol EMPRESA).
const MOTIVOS_INACTIVO_EMPRESA = [
    'impago'       => 'Impago',
    'fin_contrato' => 'Fin de contrato',
];

// Empleados (rol USUARIO), elegidos a mano.
const MOTIVOS_INACTIVO_USUARIO = [
    'vacaciones'   => 'Vacaciones',
    'baja_laboral' => 'Baja laboral',
    'baja_empresa' => 'Baja en la empresa',
];

// Lo pone el sistema al inactivar la empresa entera (ver
// inactivarUsuariosPorEmpresa()); no se elige a mano.
const MOTIVO_EMPRESA_INACTIVA = 'empresa_inactiva';


/**
 * Traduce el valor guardado en motivo_inactivo (usuarios o
 * empresas) a la etiqueta que se le muestra al usuario. Si no
 * hay motivo o no se reconoce el valor, devuelve '-' (mismo
 * criterio que el resto del listado para datos ausentes).
 */
function etiquetaMotivoInactivo(?string $valor): string
{
    $etiquetas = MOTIVOS_INACTIVO_USUARIO
        + [MOTIVO_EMPRESA_INACTIVA => 'Empresa inactiva']
        + MOTIVOS_INACTIVO_EMPRESA;

    return $etiquetas[$valor] ?? '-';
}


/**
 * Pinta los <option> de un desplegable de motivo, marcando
 * $elegido. (La opción vacía "Selecciona un motivo" la pone
 * cada formulario.)
 */
function pintarOpcionesMotivo(array $motivos, ?string $elegido): void
{
    foreach ($motivos as $valor => $texto) {
        echo '<option value="' . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . '"'
            . ($valor === $elegido ? ' selected' : '') . '>'
            . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8')
            . '</option>';
    }
}


/* =====================================================
   VALIDACIÓN DE LOS DATOS DE LA EMPRESA
   =====================================================
   Común a guardar_empresa.php (crear) y
   actualizar_empresa.php (editar). Requiere
   includes/validaciones.php.
========================================================= */


/**
 * Comprueba el formato de los datos de la empresa y devuelve
 * TODOS los errores encontrados (['mensaje', 'campo']), para
 * avisar de todo a la vez y no de uno en uno.
 *
 * $datos: nombre, cif, direccion, telefono, email, estado,
 * motivo_inactivo (ya recortados con trim).
 * $emailObligatorio: al crear, el email se reutiliza como
 * email del usuario de acceso, así que es obligatorio; al
 * editar, solo se valida si se ha escrito.
 *
 * Las comprobaciones contra la base de datos (CIF o username
 * repetidos) las hace cada página.
 */
function validarDatosEmpresa(array $datos, bool $emailObligatorio): array
{
    $errores = [];

    if (!validarNombreEmpresa($datos['nombre'])) {

        $errores[] = [
            'mensaje' => $datos['nombre'] !== '' && !preg_match('/^[\p{L}\p{N}]/u', $datos['nombre'])
                ? 'El nombre de la empresa debe empezar con una letra o un número.'
                : 'El nombre de la empresa es obligatorio y no es válido. Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.',
            'campo'   => 'nombre',
        ];

    }

    if (!validarCIF($datos['cif'])) {
        $errores[] = ['mensaje' => 'El CIF no es válido.', 'campo' => 'cif'];
    }

    if ($datos['direccion'] !== '' && !validarDireccion($datos['direccion'])) {
        $errores[] = ['mensaje' => 'La dirección no es válida.', 'campo' => 'direccion'];
    }

    if ($datos['telefono'] !== '' && !validarTelefono($datos['telefono'])) {
        $errores[] = ['mensaje' => 'El teléfono no es válido. Introduce un número nacional o internacional (7 a 15 dígitos).', 'campo' => 'telefono'];
    }

    if (($emailObligatorio || $datos['email'] !== '') && !validarEmail($datos['email'])) {
        $errores[] = ['mensaje' => 'El email no es válido.', 'campo' => 'email'];
    }

    if (!in_array($datos['estado'], ['Activo', 'Inactivo'], true)) {
        $errores[] = ['mensaje' => 'El estado seleccionado no es válido.', 'campo' => 'estado'];
    }

    if ($datos['estado'] === 'Inactivo' && !isset(MOTIVOS_INACTIVO_EMPRESA[$datos['motivo_inactivo']])) {
        $errores[] = ['mensaje' => 'Debes seleccionar un motivo.', 'campo' => 'motivo_inactivo'];
    }

    return $errores;
}


/**
 * Vuelve al formulario con todos los errores a la vez (ver
 * establecerErrorFormulario() en includes/form_flash.php),
 * resaltando el primer campo que ha fallado. No hace nada si
 * no hay errores.
 */
function volverConErroresEmpresa(array $errores, string $redirigirA): void
{
    if (empty($errores)) {
        return;
    }

    establecerErroresFormulario($errores, $_POST, $redirigirA);
}


/* =====================================================
   USUARIO DE ACCESO DE LA EMPRESA
   =====================================================
   Cada empresa tiene una cuenta de login propia: el usuario
   con rol EMPRESA que se crea junto con ella (ver
   guardar_empresa.php). La usan editar/actualizar (username),
   resetear_password_empresa.php y eliminar_empresa.php.
========================================================= */


/**
 * Usuario de acceso (rol EMPRESA) de la empresa:
 * ['id', 'username', 'cambiar_password'], o null si no tiene.
 */
function obtenerUsuarioAccesoEmpresa(PDO $pdo, int $idEmpresa): ?array
{
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.cambiar_password
        FROM usuarios u
        INNER JOIN roles r ON r.id = u.id_rol
        WHERE u.id_empresa = ?
            AND r.nombre = ?
        ORDER BY u.id
        LIMIT 1
    ");

    $stmt->execute([$idEmpresa, ROL_EMPRESA]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


/**
 * Datos de la empresa para las tarjetas de confirmación
 * (empresa creada / actualizada / eliminada) y para la
 * notificación de eliminar del listado, en el orden en que
 * se muestran. Así las tres tarjetas y el JSON salen de la
 * misma lista.
 *
 * Cada campo: ['etiqueta', 'valor', 'clase_item', 'clase_valor'].
 * El Motivo solo aparece si la empresa está Inactiva.
 */
function camposDatosEmpresa(array $empresa): array
{
    $campos = [
        ['Código de empresa', $empresa['codigo_empresa'], '', ''],
        ['Nombre', $empresa['nombre'], '', ''],
        ['CIF', $empresa['cif'], '', ''],
        ['Dirección', $empresa['direccion'] ?? 'No indicada', '', ''],
        ['Teléfono', $empresa['telefono'] ?? 'No indicado', '', ''],
        ['Email', $empresa['email'] ?? 'No indicado', '', ''],
        [
            'Estado',
            $empresa['estado'],
            'empresa-estado-item',
            $empresa['estado'] === 'Activo' ? 'text-success' : 'text-danger',
        ],
    ];

    if ($empresa['estado'] === 'Inactivo') {
        $campos[] = ['Motivo', etiquetaMotivoInactivo($empresa['motivo_inactivo']), 'empresa-motivo-item', ''];
    }

    return array_map(
        fn(array $campo): array => array_combine(['etiqueta', 'valor', 'clase_item', 'clase_valor'], $campo),
        $campos
    );
}


/* =====================================================
   CASCADA DE ESTADO: EMPRESA -> SUS USUARIOS
   =====================================================
   El usuario de acceso con rol EMPRESA ya se mantiene
   sincronizado 1:1 con su empresa en cambiar_estado_empresa.php
   / cambiar_estado_usuario.php / actualizar_empresa.php /
   actualizar_usuario.php. Estas dos funciones son la parte
   nueva: cuando la empresa se inactiva, sus empleados (rol
   USUARIO) que estuvieran Activos se inactivan también, y al
   reactivar la empresa solo se reactiva a esos mismos, nunca a
   los que ya estaban Inactivos por su cuenta (vacaciones, baja
   laboral...) antes de que la empresa se inactivara.
========================================================= */


/**
 * Aplica a los usuarios de la empresa el estado que acaba de
 * guardarse en la empresa (actualizar_empresa.php y
 * cambiar_estado_empresa.php):
 *   - su usuario de acceso (rol EMPRESA) toma el mismo estado
 *     y motivo, porque representa el login de la empresa;
 *   - sus empleados (rol USUARIO) se inactivan o reactivan en
 *     cascada (ver las dos funciones de abajo).
 */
function sincronizarUsuariosConEstadoEmpresa(PDO $pdo, int $idEmpresa, string $estado, ?string $motivo): void
{
    $pdo->prepare("
        UPDATE usuarios u
        INNER JOIN roles r ON r.id = u.id_rol
        SET u.estado = ?, u.motivo_inactivo = ?
        WHERE u.id_empresa = ?
            AND r.nombre = ?
    ")->execute([
        $estado,
        $estado === 'Inactivo' ? $motivo : null,
        $idEmpresa,
        ROL_EMPRESA,
    ]);

    if ($estado === 'Inactivo') {
        inactivarUsuariosPorEmpresa($pdo, $idEmpresa);
    } else {
        reactivarUsuariosPorEmpresa($pdo, $idEmpresa);
    }
}


/**
 * Inactiva a los usuarios (rol USUARIO) de la empresa que en
 * este momento estén Activos, marcándolos con
 * inactivo_por_empresa para poder reactivar solo a esos luego
 * (ver reactivarUsuariosPorEmpresa()). El motivo se guarda como
 * "empresa_inactiva" ("Empresa inactiva"), distinto de "Baja en
 * la empresa": ese es un motivo elegido a mano (el empleado deja
 * la empresa), este lo pone el sistema porque es la empresa
 * entera la que se ha inactivado.
 */
function inactivarUsuariosPorEmpresa(PDO $pdo, int $idEmpresa): void
{
    $pdo->prepare("
        UPDATE usuarios u
        INNER JOIN roles r ON r.id = u.id_rol
        SET u.estado = 'Inactivo',
            u.motivo_inactivo = ?,
            u.inactivo_por_empresa = 1
        WHERE u.id_empresa = ?
            AND r.nombre IN (?, ?)
            AND u.estado = 'Activo'
    ")->execute([MOTIVO_EMPRESA_INACTIVA, $idEmpresa, ...ROLES_EQUIPO_EMPRESA]);
}


/**
 * Reactiva únicamente a los usuarios de la empresa que se
 * inactivaron en cascada al inactivarse la empresa (ver
 * inactivarUsuariosPorEmpresa()). Los que estén Inactivos por
 * su cuenta no llevan esta marca y se quedan como están.
 */
function reactivarUsuariosPorEmpresa(PDO $pdo, int $idEmpresa): void
{
    $pdo->prepare("
        UPDATE usuarios
        SET estado = 'Activo',
            motivo_inactivo = NULL,
            inactivo_por_empresa = 0
        WHERE id_empresa = ?
            AND inactivo_por_empresa = 1
    ")->execute([$idEmpresa]);
}

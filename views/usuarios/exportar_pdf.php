<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('usuarios');

require_once '../../config/database.php';
require_once '../../includes/pdf_listado.php';


// =====================================================
// IDS RECIBIDOS DESDE EL LISTADO (YA FILTRADO EN EL NAVEGADOR)
// =====================================================

$ids = idsDesdePost('ids');

$usuarios = [];

if (!empty($ids)) {

    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.username,
            u.nombre,
            u.apellidos,
            u.email,
            u.telefono,
            u.creado_por,
            e.nombre AS empresa,
            r.nombre AS rol
        FROM usuarios u

        INNER JOIN empresas e
            ON u.id_empresa = e.id

        INNER JOIN roles r
            ON u.id_rol = r.id

        WHERE u.id IN ($marcadores)
    ");

    $stmt->execute($ids);

    // $ids ya viene en el orden (incluida la ordenación por
    // columna) que tenía la tabla en el navegador; el "IN (...)"
    // de SQL no garantiza devolver las filas en ese mismo orden,
    // así que se reordenan aquí para respetarlo en el PDF.
    $usuarios = ordenarSegunIds($stmt->fetchAll(PDO::FETCH_ASSOC), $ids);

}


// =====================================================
// VOLVER A COMPROBAR PERMISOS FILA A FILA
// =====================================================
//
// Los ids llegan del navegador: nunca hay que fiarse de
// ellos a ciegas. La misma regla que en el listado
// (puedeVerUsuario) decide qué usuarios puede exportar
// de verdad el rol actual.
//
// =====================================================

$usuarios = array_values(array_filter(
    $usuarios,
    fn(array $usuario): bool =>
        puedeVerUsuario(
            $usuario['creado_por'] !== null ? (int) $usuario['creado_por'] : null
        )
));


// =====================================================
// GENERAR EL PDF
// =====================================================

$filas = array_map(
    fn(array $usuario): array => [
        $usuario['nombre'] . ' ' . $usuario['apellidos'] . ' (@' . $usuario['username'] . ')',
        $usuario['email'],
        $usuario['telefono'] ?? '—',
        $usuario['rol'],
        $usuario['empresa'],
    ],
    $usuarios
);

$pdf = new ListadoPDF('L');
$pdf->tituloDocumento = 'Listado de usuarios';
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->TablaListado(
    ['Usuario', 'Email', 'Teléfono', 'Rol', 'Empresa'],
    [60, 70, 30, 35, 82],
    $filas
);

$nombreArchivo = 'usuarios_' . date('Y-m-d_His') . '.pdf';

$pdf->Output('I', $nombreArchivo);

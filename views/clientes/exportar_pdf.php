<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('clientes');

require_once '../../config/database.php';
require_once '../../includes/pdf_listado.php';


// =====================================================
// IDS RECIBIDOS DESDE EL LISTADO (YA FILTRADO EN EL NAVEGADOR)
// =====================================================

$ids = idsDesdePost('ids');

$clientes = [];

if (!empty($ids)) {

    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            id,
            nombre,
            apellidos,
            direccion,
            telefono,
            email,
            nif,
            creado_por
        FROM clientes
        WHERE id IN ($marcadores)
    ");

    $stmt->execute($ids);

    // $ids ya viene en el orden (incluida la ordenación por
    // columna) que tenía la tabla en el navegador; el "IN (...)"
    // de SQL no garantiza devolver las filas en ese mismo orden,
    // así que se reordenan aquí para respetarlo en el PDF.
    $clientes = ordenarSegunIds($stmt->fetchAll(PDO::FETCH_ASSOC), $ids);

}


// =====================================================
// VOLVER A COMPROBAR PERMISOS FILA A FILA
// =====================================================
//
// Los ids llegan del navegador: nunca hay que fiarse de
// ellos a ciegas. La misma regla que en el listado
// (puedeVerCliente) decide qué clientes puede exportar
// de verdad el rol actual.
//
// =====================================================

$clientes = array_values(array_filter(
    $clientes,
    fn(array $cliente): bool =>
        puedeVerCliente(
            $cliente['creado_por'] !== null ? (int) $cliente['creado_por'] : null
        )
));


// =====================================================
// GENERAR EL PDF
// =====================================================

$filas = array_map(
    fn(array $cliente): array => [
        $cliente['nombre'] . ' ' . $cliente['apellidos'],
        $cliente['direccion'] ?? '—',
        $cliente['telefono'] ?? '—',
        $cliente['email'],
        $cliente['nif'],
    ],
    $clientes
);

$pdf = new ListadoPDF('L');
$pdf->tituloDocumento = 'Listado de clientes';
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->TablaListado(
    ['Cliente', 'Dirección', 'Teléfono', 'Email', 'DNI/NIE'],
    [60, 87, 35, 65, 30],
    $filas
);

$nombreArchivo = 'clientes_' . date('Y-m-d_His') . '.pdf';

$pdf->Output('I', $nombreArchivo);

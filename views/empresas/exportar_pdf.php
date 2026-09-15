<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('empresas');

require_once '../../config/database.php';
require_once '../../includes/pdf_listado.php';


// =====================================================
// IDS RECIBIDOS DESDE EL LISTADO (YA FILTRADO EN EL NAVEGADOR)
// =====================================================

$ids = idsDesdePost('ids');

$empresas = [];

if (!empty($ids)) {

    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            id,
            codigo_empresa,
            nombre,
            cif,
            direccion,
            telefono,
            email,
            creado_por
        FROM empresas
        WHERE id IN ($marcadores)
    ");

    $stmt->execute($ids);

    // $ids ya viene en el orden (incluida la ordenación por
    // columna) que tenía la tabla en el navegador; el "IN (...)"
    // de SQL no garantiza devolver las filas en ese mismo orden,
    // así que se reordenan aquí para respetarlo en el PDF.
    $empresas = ordenarSegunIds($stmt->fetchAll(PDO::FETCH_ASSOC), $ids);

}


// =====================================================
// VOLVER A COMPROBAR PERMISOS FILA A FILA
// =====================================================
//
// Los ids llegan del navegador: nunca hay que fiarse de
// ellos a ciegas. La misma regla que en el listado
// (puedeVerEmpresa) decide qué empresas puede exportar
// de verdad el rol actual.
//
// =====================================================

$empresas = array_values(array_filter(
    $empresas,
    fn(array $empresa): bool =>
        puedeVerEmpresa(
            $empresa['creado_por'] !== null ? (int) $empresa['creado_por'] : null
        )
));


// =====================================================
// GENERAR EL PDF
// =====================================================

$filas = array_map(
    fn(array $empresa): array => [
        $empresa['nombre'],
        $empresa['codigo_empresa'],
        $empresa['cif'],
        $empresa['direccion'] ?? '—',
        $empresa['telefono'] ?? '—',
        $empresa['email'] ?? '—',
    ],
    $empresas
);

$pdf = new ListadoPDF('L');
$pdf->tituloDocumento = 'Listado de empresas';
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->TablaListado(
    ['Empresa', 'Código', 'CIF', 'Dirección', 'Teléfono', 'Email'],
    [55, 25, 30, 70, 30, 67],
    $filas
);

$nombreArchivo = 'empresas_' . date('Y-m-d_His') . '.pdf';

$pdf->Output('I', $nombreArchivo);

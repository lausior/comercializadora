<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('comercializadoras');

require_once '../../config/database.php';
require_once '../../includes/pdf_listado.php';


// =====================================================
// IDS RECIBIDOS DESDE EL LISTADO (YA FILTRADO EN EL NAVEGADOR)
// =====================================================

$ids = idsDesdePost('ids');

$comercializadoras = [];

if (!empty($ids)) {

    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            id,
            nombre,
            cif,
            direccion,
            telefono,
            email,
            suministra_luz,
            suministra_gas,
            creado_por
        FROM comercializadoras
        WHERE id IN ($marcadores)
    ");

    $stmt->execute($ids);

    // $ids ya viene en el orden (incluida la ordenación por
    // columna) que tenía la tabla en el navegador; el "IN (...)"
    // de SQL no garantiza devolver las filas en ese mismo orden,
    // así que se reordenan aquí para respetarlo en el PDF.
    $comercializadoras = ordenarSegunIds($stmt->fetchAll(PDO::FETCH_ASSOC), $ids);

}


// =====================================================
// VOLVER A COMPROBAR PERMISOS FILA A FILA
// =====================================================
//
// Los ids llegan del navegador: nunca hay que fiarse de
// ellos a ciegas. La misma regla que en el listado
// (puedeVerComercializadora) decide qué comercializadoras
// puede exportar de verdad el rol actual.
//
// =====================================================

$comercializadoras = array_values(array_filter(
    $comercializadoras,
    fn(array $comercializadora): bool =>
        puedeVerComercializadora(
            $comercializadora['creado_por'] !== null ? (int) $comercializadora['creado_por'] : null
        )
));


// =====================================================
// GENERAR EL PDF
// =====================================================

$filas = array_map(
    function (array $comercializadora): array {

        $servicios = [];
        if ($comercializadora['suministra_luz']) {
            $servicios[] = 'Luz';
        }
        if ($comercializadora['suministra_gas']) {
            $servicios[] = 'Gas';
        }

        return [
            $comercializadora['nombre'],
            $comercializadora['cif'],
            $comercializadora['direccion'] ?? '—',
            $comercializadora['telefono'] ?? '—',
            $comercializadora['email'] ?? '—',
            implode(' y ', $servicios),
        ];

    },
    $comercializadoras
);

$pdf = new ListadoPDF('L');
$pdf->tituloDocumento = 'Listado de comercializadoras';
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->TablaListado(
    ['Comercializadora', 'CIF', 'Dirección', 'Teléfono', 'Email', 'Servicios'],
    [44, 26, 62, 35, 68, 32],
    $filas
);

$nombreArchivo = 'comercializadoras_' . date('Y-m-d_His') . '.pdf';

$pdf->Output('I', $nombreArchivo);

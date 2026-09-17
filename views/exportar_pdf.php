<?php

session_start();

require_once __DIR__ . '/../config/permisos.php';
requerirPermiso('logs');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/pdf_listado.php';


// =====================================================
// IDS RECIBIDOS DESDE EL LISTADO (YA FILTRADO EN EL NAVEGADOR)
// =====================================================

$ids = idsDesdePost('ids');

$logs = [];

if (!empty($ids)) {

    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT
            id,
            fecha_hora,
            tipo,
            usuario,
            rol,
            evento,
            descripcion,
            ip
        FROM logs
        WHERE id IN ($marcadores)
    ");

    $stmt->execute($ids);

    // $ids ya viene en el orden (incluida la ordenación por
    // columna) que tenía la tabla en el navegador; el "IN (...)"
    // de SQL no garantiza devolver las filas en ese mismo orden,
    // así que se reordenan aquí para respetarlo en el PDF.
    $logs = ordenarSegunIds($stmt->fetchAll(PDO::FETCH_ASSOC), $ids);

}


// =====================================================
// VOLVER A COMPROBAR PERMISOS FILA A FILA
// =====================================================
//
// Los ids llegan del navegador: nunca hay que fiarse de ellos
// a ciegas. Igual que en el listado (rolesVisiblesEnLogs), no
// es un filtro por "dueño" -un log no tiene- sino por el rol
// de quien generó cada evento.
//
// =====================================================

$rolesVisibles = rolesVisiblesEnLogs();

$logs = array_values(array_filter(
    $logs,
    fn(array $log): bool => in_array($log['rol'], $rolesVisibles, true)
));


// =====================================================
// GENERAR EL PDF
// =====================================================

$filas = array_map(
    fn(array $log): array => [
        date('d/m/Y H:i:s', strtotime($log['fecha_hora'])),
        $log['tipo'],
        $log['usuario'],
        $log['evento'],
        $log['descripcion'],
        $log['ip'],
    ],
    $logs
);

$pdf = new ListadoPDF('L');
$pdf->tituloDocumento = 'Listado de logs';
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->TablaListado(
    ['Fecha y hora', 'Tipo', 'Usuario', 'Evento', 'Descripción', 'IP'],
    [34, 28, 30, 45, 110, 30],
    $filas
);

$nombreArchivo = 'logs_' . date('Y-m-d_His') . '.pdf';

$pdf->Output('I', $nombreArchivo);

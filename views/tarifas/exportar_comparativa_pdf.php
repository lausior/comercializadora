<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/tarifas.php';
require_once '../../includes/comparativa.php';
require_once '../../includes/pdf_comparativa.php';


// =====================================================
// PARÁMETROS (LOS MISMOS QUE LA COMPARATIVA EN PANTALLA)
// =====================================================
//
// Llega desde el botón "Exportar PDF" del resultado de la
// comparativa (ver $urlExportarPdf en tarifas.php):
//   servicio, peaje, id_cliente, comp[] (comercializadoras
//   marcadas) y otros (mantener sus otros conceptos).
//
// =====================================================

$servicio = in_array($_GET['servicio'] ?? '', SERVICIOS_TARIFA, true) ? $_GET['servicio'] : 'luz';
$peaje = $_GET['peaje'] ?? '';

if (!in_array($peaje, peajesServicio($servicio), true)) {
    $peaje = peajesServicio($servicio)[0];
}

$idCliente = (int) ($_GET['id_cliente'] ?? 0);
$idsComercializadoras = array_map('intval', (array) ($_GET['comp'] ?? []));
$incluirOtros = ($_GET['otros'] ?? '') === '1';

$datos = prepararComparativaCliente($pdo, $servicio, $peaje, $idCliente, $idsComercializadoras, $incluirOtros);

$comparativa = $datos['comparativa'];

$motivoError = $datos['aviso']
    ?? (!empty($comparativa['faltan_datos'])
        ? 'A la factura del cliente le faltan ' . implode(', ', $comparativa['faltan_datos']) . '.'
        : (empty($comparativa['ofertas']) ? 'No hay ninguna tarifa con la que comparar.' : null));

if ($motivoError !== null) {

    die('
        <h2>No se puede generar el informe</h2>
        <p>' . htmlspecialchars($motivoError) . '</p>
        <p><a href="' . htmlspecialchars(urlRejillaTarifas($servicio, $peaje, true, $idCliente)) . '">Volver a Tarifas</a></p>
    ');

}


// =====================================================
// DATOS DEL CLIENTE
// =====================================================

$stmtCliente = $pdo->prepare("
    SELECT nombre, apellidos, nif, direccion, telefono, email
    FROM clientes
    WHERE id = ?
");

$stmtCliente->execute([$idCliente]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

$tarifa = $datos['tarifa_cliente'];
$ofertas = $comparativa['ofertas'];
$mejor = $ofertas[0];
$actual = $comparativa['actual'];
$totalReferencia = $comparativa['total_referencia'];
$dias = $comparativa['dias'];

$nombreCliente = trim($cliente['nombre'] . ' ' . $cliente['apellidos']);
$etiquetaServicio = $servicio === 'gas' ? 'Gas' : 'Luz';
$porcentaje = fn($valor): string => rtrim(rtrim(number_format((float) $valor, 2, ',', ''), '0'), ',');
$conSigno = fn(float $importe): string => ($importe > 0 ? '+' : '') . formatearEuros($importe);
$noIndicado = fn(?string $valor): string => $valor !== null && trim($valor) !== '' ? $valor : 'No indicado';


// =====================================================
// INFORME
// =====================================================

$pdf = new ComparativaPDF();
$pdf->referencia = 'NG-' . date('Ymd') . '-' . $tarifa['id'];
$pdf->SetTitle(textoPdf('Estudio comparativo de tarifas - ' . $nombreCliente));
$pdf->SetAuthor(textoPdf(EMPRESA_INFORME['razon_social']));
$pdf->AddPage();

$pdf->Titulo(
    'Estudio comparativo de tarifas',
    'Suministro de ' . mb_strtolower($etiquetaServicio, 'UTF-8') . ' · peaje ' . $peaje,
    date('d/m/Y')
);


// ---------------------------------------------------
// 1. Datos del cliente
// ---------------------------------------------------

$pdf->Seccion('Datos del cliente');

$pdf->DatosEnColumnas([
    ['Nombre', $nombreCliente],
    ['NIF', $cliente['nif']],
    ['Dirección', $noIndicado($cliente['direccion'])],
    ['Teléfono', $noIndicado($cliente['telefono'])],
    ['Email', $noIndicado($cliente['email'])],
    ['Suministro', $etiquetaServicio . ' ' . $peaje],
]);


// ---------------------------------------------------
// 2. Tarifa actual y datos de su factura
// ---------------------------------------------------

$pdf->Seccion('Tarifa actual');

if ($servicio === 'gas') {

    $consumo = formatearNumero((string) $tarifa['consumo_gas'], 0) . ' kWh';

    $pdf->DatosEnColumnas([
        ['Tarifa', $tarifa['nombre'] !== '' ? $tarifa['nombre'] : 'Sin nombre'],
        ['Periodo facturado', $dias . ' días'],
        ['Consumo', $consumo],
        ['Término fijo', formatearPrecio($tarifa['termino_fijo']) . ' €/día'],
        ['Término variable', formatearPrecio($tarifa['termino_variable']) . ' €/kWh'],
        ['Total factura', formatearEuros($totalReferencia)],
    ]);

} else {

    $franjas = PEAJES_LUZ[$peaje];

    $consumoTotal = 0.0;
    $potencias = [];

    for ($i = 1; $i <= $franjas['energia']; $i++) {
        $consumoTotal += (float) ($tarifa['consumo_p' . $i] ?? 0);
    }

    for ($i = 1; $i <= $franjas['potencia']; $i++) {
        $potencias[] = formatearNumero((string) ($tarifa['potencia_contratada_p' . $i] ?? '0'), 0);
    }

    // "15 en todas las franjas" si son iguales (lo habitual);
    // si no, "P1-P6: 15 / 15 / 10..." (con 6 franjas no cabe
    // "P1 15 · P2 15 · ...").
    $textoPotencia = count(array_unique($potencias)) === 1
        ? $potencias[0] . ' kW en todas las franjas'
        : 'P1-P' . count($potencias) . ': ' . implode(' / ', $potencias) . ' kW';

    $pdf->DatosEnColumnas([
        ['Tarifa', $tarifa['nombre'] !== '' ? $tarifa['nombre'] : 'Sin nombre'],
        ['Periodo facturado', $dias . ' días'],
        ['Consumo total', formatearNumero((string) round($consumoTotal, 3), 0) . ' kWh'],
        ['Potencia', $textoPotencia],
        ['Impuestos', 'IVA ' . $porcentaje($tarifa['iva'] ?? IVA_POR_DEFECTO) . ' % · IE ' . $porcentaje($tarifa['impuesto_electrico'] ?? IMPUESTO_ELECTRICO_POR_DEFECTO) . ' %'],
        ['Total factura', formatearEuros($totalReferencia)],
    ]);

    // Precios y consumo por franja.
    $numeroFranjas = max($franjas['energia'], $franjas['potencia']);
    $anchoFranja = (ComparativaPDF::ANCHO - 45) / $numeroFranjas;

    $filaPrecios = function (string $etiqueta, string $prefijo, int $franjasTermino, callable $formato) use ($tarifa, $numeroFranjas): array {

        $fila = [$etiqueta];

        for ($i = 1; $i <= $numeroFranjas; $i++) {
            $valor = $tarifa[$prefijo . $i] ?? null;
            $fila[] = $i <= $franjasTermino && $valor !== null ? $formato((string) $valor) : '—';
        }

        return $fila;

    };

    $pdf->Tabla(
        array_merge(['Por franja'], array_map(fn(int $i): string => 'P' . $i, range(1, $numeroFranjas))),
        array_merge([45], array_fill(0, $numeroFranjas, $anchoFranja)),
        [
            $filaPrecios('Energía (€/kWh)', 'energia_p', $franjas['energia'], 'formatearPrecio'),
            $filaPrecios('Potencia (€/kW día)', 'potencia_p', $franjas['potencia'], 'formatearPrecio'),
            $filaPrecios('Consumo (kWh)', 'consumo_p', $franjas['energia'], fn(string $valor): string => formatearNumero($valor, 0)),
        ],
        array_merge(['L'], array_fill(0, $numeroFranjas, 'R'))
    );

}


// ---------------------------------------------------
// 3. Comparativa con la mejor opción
// ---------------------------------------------------

// Las ofertas se presentan como propuestas de NG Asesores,
// que hace de buscador de ofertas: en el informe NO aparecen
// ni la comercializadora ni el nombre de la tarifa ofertada
// (muchos nombres la delatan). Solo se nombra la tarifa
// actual del cliente. En pantalla (tarifas.php) sí se ven,
// para uso interno.
$nombrePropuesta = fn(int $posicion): string => 'Propuesta ' . EMPRESA_INFORME['nombre'] . ' ' . ($posicion + 1);
$textoTarifaActual = $tarifa['nombre'] !== '' ? ': ' . $tarifa['nombre'] : '';

$pdf->Seccion('Comparativa con la mejor propuesta');

$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(ComparativaPDF::ANCHO, 6, textoPdf(
    $mejor['ahorro'] > 0
        ? 'Mejor propuesta de ' . EMPRESA_INFORME['nombre'] . ' frente a su tarifa actual' . $textoTarifaActual . '.'
        : 'Propuesta más económica de ' . EMPRESA_INFORME['nombre'] . ' frente a su tarifa actual' . $textoTarifaActual . '.'
), 0, 1);
$pdf->Ln(1);

$filasComparativa = [];

foreach ($comparativa['columnas'] as $columna) {

    $importeActual = $actual['lineas'][$columna];
    $importeMejor = $mejor['simulacion']['lineas'][$columna];

    $filasComparativa[] = [$columna, formatearEuros($importeActual), formatearEuros($importeMejor), $conSigno($importeMejor - $importeActual)];

}

$filasComparativa[] = ['IVA', formatearEuros($actual['iva']), formatearEuros($mejor['simulacion']['iva']), $conSigno($mejor['simulacion']['iva'] - $actual['iva'])];
$filasComparativa[] = ['Total factura', formatearEuros($totalReferencia), formatearEuros($mejor['simulacion']['total']), $conSigno(-$mejor['ahorro'])];

$pdf->Tabla(
    ['Concepto', 'Tarifa actual', 'Propuesta NG', 'Diferencia'],
    [72, 36, 36, 36],
    $filasComparativa,
    ['L', 'R', 'R', 'R'],
    [count($filasComparativa) - 1]
);

if ($mejor['ahorro'] > 0) {

    $pdf->Destacado(
        'Ahorro estimado: ' . formatearEuros($mejor['ahorro']) . ' por factura',
        'Unos ' . formatearEuros(ahorroAnual($mejor['ahorro'], $dias)) . ' al año con la propuesta de '
            . EMPRESA_INFORME['nombre'] . ' (' . $porcentaje($mejor['ahorro'] / $totalReferencia * 100) . ' % menos que la factura actual).'
    );

} else {

    $pdf->Destacado(
        'Ninguna propuesta mejora la tarifa actual',
        'La más económica costaría ' . formatearEuros(-$mejor['ahorro']) . ' más por factura que la actual ('
            . formatearEuros($totalReferencia) . ').',
        false
    );

}


// ---------------------------------------------------
// 4. Todas las ofertas analizadas
// ---------------------------------------------------

$pdf->Seccion('Propuestas analizadas');

$filasOfertas = [];

foreach ($ofertas as $posicion => $oferta) {
    $filasOfertas[] = [
        $posicion + 1,
        $nombrePropuesta($posicion) . ($posicion === 0 && $oferta['ahorro'] > 0 ? ' (recomendada)' : ''),
        formatearEuros($oferta['simulacion']['total']),
        $conSigno($oferta['ahorro']),
        $conSigno(ahorroAnual($oferta['ahorro'], $dias)),
    ];
}

$pdf->Tabla(
    ['#', 'Propuesta', 'Total factura', 'Ahorro factura', 'Ahorro anual'],
    [10, 80, 30, 30, 30],
    $filasOfertas,
    ['C', 'L', 'R', 'R', 'R'],
    [],
    $mejor['ahorro'] > 0 ? 0 : null
);

// Las que no se han podido calcular (faltan precios) solo se
// cuentan, sin nombrarlas.
if (!empty($comparativa['incompletas'])) {

    $numeroIncompletas = count($comparativa['incompletas']);

    $pdf->Notas([
        $numeroIncompletas . ($numeroIncompletas === 1 ? ' propuesta no se ha podido' : ' propuestas no se han podido')
            . ' comparar por falta de precios.',
    ]);

    $pdf->Ln(2);

}


// ---------------------------------------------------
// 5. Notas
// ---------------------------------------------------

$pdf->Ln(2);

$pdf->Notas([
    'Estimación calculada con los consumos' . ($servicio === 'luz' ? ' y potencias' : '') . ' de la factura actual del cliente ('
        . $dias . ' días) y los precios de las propuestas vigentes a ' . date('d/m/Y') . '.',
    $servicio === 'luz'
        ? 'Se mantienen el alquiler del contador, la energía reactiva, los excesos de potencia, el IVA y el impuesto eléctrico de la factura actual.'
        : 'Se mantienen el alquiler del contador y el IVA de la factura actual; incluye el impuesto de hidrocarburos.',
    $incluirOtros
        ? 'Las propuestas incluyen los "otros conceptos" de la factura actual.'
        : 'Las propuestas no incluyen los "otros conceptos" de la factura actual (servicios de su comercializadora actual).',
    'El ahorro anual es una extrapolación de esta factura y puede variar según el consumo real. Documento informativo sin valor contractual.',
]);


// Nombre del archivo: Comparativa_Nombre_Cliente_AAAAMMDD.pdf
$nombreArchivo = 'Comparativa_' . preg_replace('/[^A-Za-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $nombreCliente)) . '_' . date('Ymd') . '.pdf';

$pdf->Output('I', $nombreArchivo);

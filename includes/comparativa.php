<?php

/**
 * =====================================================
 * COMPARATIVA: SIMULAR LA FACTURA DE UN CLIENTE
 * =====================================================
 *
 * Calcula cuánto pagaría un cliente (sus consumos, potencias,
 * días... de datos_factura) con los precios de una tarifa:
 * la suya actual o la de una comercializadora. La usa el
 * botón "Comparar" de views/tarifas/tarifas.php (resultado en
 * views/tarifas/resultado_comparativa.php).
 *
 * Misma fórmula que el Excel de simulación:
 *   Luz: energía + potencia - compensación de excedentes
 *        + energía reactiva + excesos de potencia
 *        -> impuesto eléctrico sobre todo ello
 *        + alquiler de contador (+ otros conceptos)
 *        -> IVA
 *   Gas: término fijo + término variable + impuesto de
 *        hidrocarburos + alquiler de contador (+ otros)
 *        -> IVA
 * Cada línea se redondea a céntimos, como en la factura.
 *
 * Requiere includes/tarifas.php.
 *
 * =====================================================
 */

// Impuesto especial de hidrocarburos del gas natural para
// uso doméstico/comercial (€/kWh).
const IMPUESTO_HIDROCARBUROS_KWH = 0.00234;


/**
 * Valor numérico de un dato (null o vacío -> null).
 */
function numeroComparativa($valor): ?float
{
    return $valor === null || $valor === '' ? null : (float) $valor;
}


/**
 * Simula la factura con los precios de $tarifa (fila de
 * `tarifas`) y los datos de consumo de $factura (fila de
 * `datos_factura` del cliente).
 *
 * Devuelve:
 *   'lineas' -> [etiqueta => importe] del desglose
 *   'base'   -> base imponible
 *   'iva'    -> importe del IVA
 *   'total'  -> total con IVA
 *   'faltan' -> precios que la tarifa no tiene y harían falta
 *               para este cliente (si no está vacío, el
 *               total no es fiable)
 */
function simularFactura(array $tarifa, array $factura, string $servicio, string $peaje, bool $incluirOtros): array
{
    $dias = (int) ($factura['dias'] ?? 0);
    $faltan = [];
    $lineas = [];

    // Importe = cantidad * precio, redondeado; si hay cantidad
    // pero la tarifa no tiene ese precio, se apunta como falta.
    $importe = function (?float $cantidad, ?float $precio, string $nombre) use (&$faltan): float {

        if ($cantidad === null || $cantidad == 0.0) {
            return 0.0;
        }

        if ($precio === null) {
            $faltan[] = $nombre;
            return 0.0;
        }

        return round($cantidad * $precio, 2);

    };

    $contador = numeroComparativa($factura['alquiler_contador'] ?? null) ?? 0.0;
    $otros = $incluirOtros ? (numeroComparativa($factura['otros_conceptos'] ?? null) ?? 0.0) : 0.0;
    $porcentajeIva = numeroComparativa($factura['iva'] ?? null) ?? (float) IVA_POR_DEFECTO;

    if ($servicio === 'gas') {

        $consumo = numeroComparativa($factura['consumo_gas'] ?? null);

        $lineas['Término fijo'] = $importe((float) $dias, numeroComparativa($tarifa['termino_fijo'] ?? null), 'término fijo');
        $lineas['Término variable'] = $importe($consumo, numeroComparativa($tarifa['termino_variable'] ?? null), 'término variable');
        $lineas['Imp. hidrocarburos'] = round(($consumo ?? 0) * IMPUESTO_HIDROCARBUROS_KWH, 2);
        $lineas['Contador y otros'] = round($contador + $otros, 2);

        $base = round(array_sum($lineas), 2);

    } else {

        $franjas = PEAJES_LUZ[$peaje] ?? ['energia' => 0, 'potencia' => 0];

        $energia = 0.0;

        for ($i = 1; $i <= $franjas['energia']; $i++) {
            $energia += $importe(
                numeroComparativa($factura['consumo_p' . $i] ?? null),
                numeroComparativa($tarifa['energia_p' . $i] ?? null),
                'energía P' . $i
            );
        }

        $potencia = 0.0;

        for ($i = 1; $i <= $franjas['potencia']; $i++) {

            $kw = numeroComparativa($factura['potencia_contratada_p' . $i] ?? null);

            $potencia += $importe(
                $kw !== null ? $kw * $dias : null,
                numeroComparativa($tarifa['potencia_p' . $i] ?? null),
                'potencia P' . $i
            );

        }

        // La compensación de excedentes no puede superar el
        // término de energía (como en el Excel y en la factura).
        $excedentesKwh = numeroComparativa($factura['excedentes_kwh'] ?? null) ?? 0.0;
        $precioExcedentes = numeroComparativa($tarifa['precio_excedentes'] ?? null);
        $compensacion = $precioExcedentes !== null
            ? min(round($excedentesKwh * $precioExcedentes, 2), round($energia, 2))
            : 0.0;

        $reactivaExcesos = (numeroComparativa($factura['energia_reactiva'] ?? null) ?? 0.0)
            + (numeroComparativa($factura['excesos_potencia'] ?? null) ?? 0.0);

        $baseImpuesto = round($energia - $compensacion + $potencia + $reactivaExcesos, 2);

        $porcentajeIe = numeroComparativa($factura['impuesto_electrico'] ?? null) ?? (float) IMPUESTO_ELECTRICO_POR_DEFECTO;

        $lineas['Energía'] = round($energia, 2);
        $lineas['Potencia'] = round($potencia, 2);
        $lineas['Compensación excedentes'] = $compensacion > 0 ? -$compensacion : 0.0;
        $lineas['Reactiva y excesos'] = round($reactivaExcesos, 2);
        $lineas['Imp. eléctrico'] = round($baseImpuesto * $porcentajeIe / 100, 2);
        $lineas['Contador y otros'] = round($contador + $otros, 2);

        $base = round(array_sum($lineas), 2);

    }

    $iva = round($base * $porcentajeIva / 100, 2);

    return [
        'lineas' => $lineas,
        'base'   => $base,
        'iva'    => $iva,
        'total'  => round($base + $iva, 2),
        'faltan' => array_values(array_unique($faltan)),
    ];
}


/**
 * Datos que le faltan a la factura del cliente para poder
 * compararla (lista de textos; vacía si se puede comparar).
 */
function datosQueFaltanFactura(array $factura, string $servicio, string $peaje): array
{
    $faltan = [];

    if ((int) ($factura['dias'] ?? 0) <= 0) {
        $faltan[] = 'los días del periodo';
    }

    if ($servicio === 'gas') {

        if (numeroComparativa($factura['consumo_gas'] ?? null) === null) {
            $faltan[] = 'el consumo';
        }

        return $faltan;

    }

    $franjas = PEAJES_LUZ[$peaje] ?? ['energia' => 0, 'potencia' => 0];

    $hayConsumo = false;

    for ($i = 1; $i <= $franjas['energia']; $i++) {
        if (numeroComparativa($factura['consumo_p' . $i] ?? null) !== null) {
            $hayConsumo = true;
        }
    }

    if (!$hayConsumo) {
        $faltan[] = 'los consumos';
    }

    $hayPotencia = false;

    for ($i = 1; $i <= $franjas['potencia']; $i++) {
        if (numeroComparativa($factura['potencia_contratada_p' . $i] ?? null) !== null) {
            $hayPotencia = true;
        }
    }

    if (!$hayPotencia) {
        $faltan[] = 'la potencia contratada';
    }

    return $faltan;
}


/**
 * Compara la factura de un cliente con varias tarifas de
 * comercializadoras (ver el resultado en
 * views/tarifas/resultado_comparativa.php).
 *
 * $tarifaCliente: su tarifa actual con los datos de su factura
 *                 (fila de obtenerTarifasRejilla() en el ámbito
 *                 'clientes').
 * $tarifasOfertas: tarifas activas de las comercializadoras
 *                  elegidas, cada una con 'nombre_comercializadora'.
 * $incluirOtros: mantener sus "otros conceptos" en las ofertas.
 *
 * Devuelve:
 *   'faltan_datos' -> lo que le falta a su factura (si no está
 *                     vacío, no hay nada más)
 *   'dias', 'actual' (simulación con sus precios),
 *   'total_referencia' (total de su factura o, si no lo tiene,
 *                       el recalculado),
 *   'ofertas' (de más barata a más cara), 'incompletas'
 *   (tarifas a las que les faltan precios), 'columnas' (las
 *   del desglose que aplican a este cliente).
 */
function compararFacturaCliente(array $tarifaCliente, string $servicio, string $peaje, array $tarifasOfertas, bool $incluirOtros): array
{
    $faltanDatos = datosQueFaltanFactura($tarifaCliente, $servicio, $peaje);

    if (!empty($faltanDatos)) {
        return ['faltan_datos' => $faltanDatos];
    }

    // Su tarifa actual, recalculada con la misma fórmula (con
    // sus otros conceptos: los paga hoy).
    $actual = simularFactura($tarifaCliente, $tarifaCliente, $servicio, $peaje, true);

    $totalReferencia = numeroComparativa($tarifaCliente['total_factura'] ?? null) ?? $actual['total'];

    $ofertas = [];
    $incompletas = [];

    foreach ($tarifasOfertas as $tarifa) {

        $simulacion = simularFactura($tarifa, $tarifaCliente, $servicio, $peaje, $incluirOtros);

        $fila = [
            'comercializadora' => $tarifa['nombre_comercializadora'],
            'tarifa'           => $tarifa['nombre'] !== '' ? $tarifa['nombre'] : 'Sin nombre',
            'simulacion'       => $simulacion,
            'ahorro'           => round($totalReferencia - $simulacion['total'], 2),
        ];

        if (empty($simulacion['faltan'])) {
            $ofertas[] = $fila;
        } else {
            $incompletas[] = $fila;
        }

    }

    usort($ofertas, fn(array $a, array $b): int => $a['simulacion']['total'] <=> $b['simulacion']['total']);

    // Columnas del desglose, sin las que no aplican a este
    // cliente (sin excedentes / sin reactiva ni excesos).
    $columnas = array_keys($actual['lineas']);

    if ($servicio === 'luz' && (numeroComparativa($tarifaCliente['excedentes_kwh'] ?? null) ?? 0) == 0) {
        $columnas = array_diff($columnas, ['Compensación excedentes']);
    }

    if ($servicio === 'luz' && $actual['lineas']['Reactiva y excesos'] == 0) {
        $columnas = array_diff($columnas, ['Reactiva y excesos']);
    }

    return [
        'faltan_datos'     => [],
        'dias'             => (int) $tarifaCliente['dias'],
        'actual'           => $actual,
        'total_referencia' => $totalReferencia,
        'ofertas'          => $ofertas,
        'incompletas'      => $incompletas,
        'columnas'         => array_values($columnas),
    ];
}


/**
 * Todo lo necesario para comparar la factura de un cliente
 * con las tarifas activas de las comercializadoras elegidas
 * (mismo servicio y peaje). Lo usan el botón "Comparar" de
 * tarifas.php y la exportación a PDF, para que den lo mismo.
 *
 * Respeta los permisos: solo entran el cliente y las
 * comercializadoras que puede ver el rol actual.
 *
 * Devuelve:
 *   'cliente'        -> titular (obtenerTitularesTarifas()) o null
 *   'tarifa_cliente' -> su tarifa actual con su factura, o null
 *   'aviso'          -> por qué no se puede comparar, o null
 *   'comparativa'    -> compararFacturaCliente(), o null
 */
function prepararComparativaCliente(PDO $pdo, string $servicio, string $peaje, int $idCliente, array $idsComercializadoras, bool $incluirOtros): array
{
    $resultado = ['cliente' => null, 'tarifa_cliente' => null, 'aviso' => null, 'comparativa' => null];

    $clientes = obtenerTitularesTarifas($pdo, 'clientes', $servicio);
    $resultado['cliente'] = $clientes[$idCliente] ?? null;

    if ($resultado['cliente'] === null) {
        $resultado['aviso'] = 'Elige un cliente para comparar.';
        return $resultado;
    }

    $tarifasCliente = obtenerTarifasRejilla($pdo, 'clientes', $servicio, $peaje, [$idCliente]);
    $resultado['tarifa_cliente'] = $tarifasCliente ? reset($tarifasCliente) : null;

    if ($resultado['tarifa_cliente'] === null) {
        $resultado['aviso'] = 'Guarda primero la tarifa actual de ' . $resultado['cliente']['nombre'] . ' para poder comparar.';
        return $resultado;
    }

    $comercializadoras = obtenerTitularesTarifas($pdo, 'comercializadoras', $servicio);
    $idsComercializadoras = array_values(array_intersect($idsComercializadoras, array_keys($comercializadoras)));

    if (empty($idsComercializadoras)) {
        $resultado['aviso'] = 'Marca al menos una comercializadora en la tabla de abajo y vuelve a pulsar Comparar.';
        return $resultado;
    }

    $ofertas = [];

    foreach (obtenerTarifasRejilla($pdo, 'comercializadoras', $servicio, $peaje, $idsComercializadoras) as $tarifa) {

        if ((int) $tarifa['activa'] === 1) {
            $tarifa['nombre_comercializadora'] = $comercializadoras[(int) $tarifa['id_titular']]['nombre'];
            $ofertas[] = $tarifa;
        }

    }

    $resultado['comparativa'] = compararFacturaCliente($resultado['tarifa_cliente'], $servicio, $peaje, $ofertas, $incluirOtros);

    return $resultado;
}


/**
 * Ahorro anual estimado a partir del ahorro de una factura
 * de $dias días.
 */
function ahorroAnual(float $ahorroFactura, int $dias): float
{
    return $dias > 0 ? round($ahorroFactura / $dias * 365, 2) : 0.0;
}


/**
 * Formato de importe para pantalla: "1.234,56 €".
 */
function formatearEuros(float $importe): string
{
    return number_format($importe, 2, ',', '.') . ' €';
}

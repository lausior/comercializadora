<?php

/**
 * =====================================================
 * TARIFAS: PEAJES, COLUMNAS, VALORES Y VALIDACIÓN
 * =====================================================
 *
 * Lo comparten la rejilla de tarifas (tarifas.php), su
 * guardado (guardar_tarifas.php) y el borrado
 * (eliminar_tarifa.php), para que las columnas de cada
 * servicio/peaje y la forma de leer/escribir valores estén
 * definidas en un solo sitio.
 *
 * La rejilla tiene dos vistas (ámbitos):
 *   'comercializadoras' -> las tarifas que ofrece cada una
 *   'clientes'          -> la tarifa que cada cliente tiene
 *                          contratada hoy
 *
 * Además de los precios, algunas rejillas llevan columnas de
 * datos de factura (consumos, potencias, días, IVA...), que
 * se guardan en `datos_factura` (ver tieneDatosFactura()).
 *
 * =====================================================
 */


// -------------------------------------------------------
// Peajes de cada servicio
// -------------------------------------------------------
//
// Luz: franjas de energía / potencia que usa cada peaje.
// Gas: todos los peajes tienen las mismas columnas (término
// fijo y variable); cambia solo el tramo de consumo anual.

const PEAJES_LUZ = [
    '2.0TD' => ['energia' => 3, 'potencia' => 3],
    '3.0TD' => ['energia' => 6, 'potencia' => 6],
    '6.1TD' => ['energia' => 6, 'potencia' => 6],
];

const PEAJES_GAS = [
    'RL.1' => 'Hasta 5.000 kWh/año',
    'RL.2' => '5.000 - 15.000 kWh/año',
    'RL.3' => '15.000 - 50.000 kWh/año',
    'RL.4' => '50.000 - 300.000 kWh/año',
];

const SERVICIOS_TARIFA = ['luz', 'gas'];
const AMBITOS_TARIFA = ['comercializadoras', 'clientes'];


// -------------------------------------------------------
// Opciones de los desplegables de los datos de factura
// -------------------------------------------------------
//
// Clave = valor que se guarda (%), valor = texto que se ve.

const OPCIONES_IVA = [
    '5'  => '5 %',
    '10' => '10 %',
    '21' => '21 %',
];

const OPCIONES_IMPUESTO_ELECTRICO = [
    '0.5'        => '0,5 %',
    '2.5'        => '2,5 %',
    '5.11269632' => '5,11 %',
];

const IVA_POR_DEFECTO = '21';
const IMPUESTO_ELECTRICO_POR_DEFECTO = '5.11269632';

// Máximo de franjas de cualquier peaje de luz (columnas _p1.._p6).
const TARIFA_MAX_PERIODOS = 6;

// Tope de precio (€/kWh, €/kW·día o €/día) para cazar
// errores de tecleo como "1099" en vez de "0,1099".
const TARIFA_PRECIO_MAXIMO = 100;


// -------------------------------------------------------
// Tipos de valor de las columnas
// -------------------------------------------------------
//
//   precio   -> €/kWh, €/kW día, €/día: hasta 6 decimales
//   cantidad -> kWh, kW: hasta 3 decimales
//   importe  -> €: hasta 2 decimales
//   dias     -> número entero de días (1-366)
//   select   -> desplegable (IVA, impuesto eléctrico)

const TIPOS_VALOR_TARIFA = [
    'precio'   => ['patron' => '/^\d{1,4}(\.\d{1,6})?$/', 'ejemplo' => '0,1099', 'decimales_minimos' => 2],
    'cantidad' => ['patron' => '/^\d{1,9}(\.\d{1,3})?$/', 'ejemplo' => '85,59', 'decimales_minimos' => 0],
    'importe'  => ['patron' => '/^\d{1,7}(\.\d{1,2})?$/', 'ejemplo' => '0,83', 'decimales_minimos' => 2],
    'dias'     => ['patron' => '/^\d{1,3}$/', 'ejemplo' => '31', 'decimales_minimos' => 0],
];


/**
 * Nombres de los peajes de un servicio ('2.0TD', ... o
 * 'RL.1', ...).
 */
function peajesServicio(string $servicio): array
{
    return array_keys($servicio === 'gas' ? PEAJES_GAS : PEAJES_LUZ);
}


/**
 * Nombres de columna por franja: energia_p1 ... energia_p6
 * (vale para cualquier prefijo: potencia, consumo...).
 */
function columnasTermino(string $termino): array
{
    $columnas = [];

    for ($i = 1; $i <= TARIFA_MAX_PERIODOS; $i++) {
        $columnas[] = $termino . '_p' . $i;
    }

    return $columnas;
}


/**
 * Columnas de precio de la tabla `tarifas` (cualquier
 * servicio y peaje).
 */
function todasColumnasPrecio(): array
{
    return array_merge(
        columnasTermino('energia'),
        columnasTermino('potencia'),
        ['precio_excedentes', 'termino_fijo', 'termino_variable']
    );
}


/**
 * ¿Lleva la rejilla columnas de datos de factura?
 * Todas las de clientes y las de luz de las
 * comercializadoras (las de gas de las comercializadoras,
 * solo precios).
 */
function tieneDatosFactura(string $servicio, string $ambito): bool
{
    return $ambito === 'clientes' || $servicio === 'luz';
}


/**
 * Columnas de la tabla `datos_factura` (sin id_tarifa).
 */
function todasColumnasFactura(): array
{
    return array_merge(
        columnasTermino('consumo'),
        columnasTermino('potencia_contratada'),
        [
            'consumo_gas', 'dias', 'excedentes_kwh', 'alquiler_contador', 'energia_reactiva',
            'excesos_potencia', 'otros_conceptos', 'iva', 'impuesto_electrico', 'total_factura',
        ]
    );
}


/**
 * Una columna de la rejilla.
 */
function columnaRejilla(string $etiqueta, string $tipo, bool $obligatorio, string $cabecera = '', array $extra = []): array
{
    return [
        'cabecera'    => $cabecera,
        'etiqueta'    => $etiqueta,
        'tipo'        => $tipo,
        'obligatorio' => $obligatorio,
    ] + $extra;
}


/**
 * Columnas "P1..Pn" de un grupo por franjas (energía,
 * potencia, consumo...).
 */
function columnasPorFranjas(string $prefijo, int $franjas, string $etiqueta, string $tipo, bool $obligatorio): array
{
    $columnas = [];

    foreach (array_slice(columnasTermino($prefijo), 0, $franjas) as $indice => $columna) {

        $periodo = 'P' . ($indice + 1);

        $columnas[$columna] = columnaRejilla($etiqueta . ' ' . $periodo, $tipo, $obligatorio, $periodo);

    }

    return $columnas;
}


/**
 * Columnas de la rejilla para un servicio/peaje/ámbito, en
 * grupos: cada grupo es una cabecera ("Energía (€/kWh)" con
 * sus P1..Pn debajo, o una columna suelta).
 *
 * Cada columna: ['cabecera' => 'P1', 'etiqueta' => para los
 * mensajes de error, 'tipo' => ver TIPOS_VALOR_TARIFA o
 * 'select', 'obligatorio' => bool, y 'opciones'/'por_defecto'
 * en los desplegables].
 *
 * Los grupos con 'factura' => true son los datos de factura
 * (solo en las rejillas que los llevan, ver
 * tieneDatosFactura()).
 */
function gruposColumnasTarifa(string $servicio, string $peaje, string $ambito = 'comercializadoras'): array
{
    $grupos = [];

    // ---------------------------------------------------
    // PRECIOS DE LA TARIFA
    // ---------------------------------------------------

    if ($servicio === 'gas') {

        $grupos[] = ['titulo' => 'Término fijo (€/día)', 'clase' => '', 'columnas' => [
            'termino_fijo' => columnaRejilla('el término fijo', 'precio', false),
        ]];

        $grupos[] = ['titulo' => 'Término variable (€/kWh)', 'clase' => '', 'columnas' => [
            'termino_variable' => columnaRejilla('el término variable', 'precio', false),
        ]];

    } else {

        $franjas = PEAJES_LUZ[$peaje] ?? ['energia' => 0, 'potencia' => 0];

        $grupos[] = [
            'titulo'   => 'Energía (€/kWh)',
            'clase'    => 'grupo-energia',
            'columnas' => columnasPorFranjas('energia', $franjas['energia'], 'el precio de energía', 'precio', false),
        ];

        $grupos[] = [
            'titulo'   => 'Potencia (€/kW día)',
            'clase'    => 'grupo-potencia',
            'columnas' => columnasPorFranjas('potencia', $franjas['potencia'], 'el precio de potencia', 'precio', false),
        ];

        $grupos[] = ['titulo' => 'Pago excedentes (€/kWh)', 'clase' => '', 'columnas' => [
            'precio_excedentes' => columnaRejilla('el pago de excedentes', 'precio', false),
        ]];

    }

    if (!tieneDatosFactura($servicio, $ambito)) {
        return $grupos;
    }

    // ---------------------------------------------------
    // DATOS DE FACTURA
    // ---------------------------------------------------
    //
    // Como el resto de columnas, ninguna es obligatoria: se
    // puede guardar una fila a medias y completarla después.
    // Solo se valida el formato de lo que se escribe.

    $factura = [];

    if ($servicio === 'gas') {

        $factura[] = ['titulo' => 'Consumo (kWh)', 'columnas' => [
            'consumo_gas' => columnaRejilla('el consumo', 'cantidad', false),
        ]];

    } else {

        $factura[] = [
            'titulo'   => 'Consumo (kWh)',
            'columnas' => columnasPorFranjas('consumo', $franjas['energia'], 'el consumo', 'cantidad', false),
        ];

        $factura[] = [
            'titulo'   => 'Potencia contratada (kW)',
            'columnas' => columnasPorFranjas('potencia_contratada', $franjas['potencia'], 'la potencia contratada', 'cantidad', false),
        ];

    }

    $factura[] = ['titulo' => 'Días', 'columnas' => [
        'dias' => columnaRejilla('los días del periodo', 'dias', false),
    ]];

    if ($servicio === 'luz') {

        $factura[] = ['titulo' => 'Excedentes (kWh)', 'columnas' => [
            'excedentes_kwh' => columnaRejilla('la energía excedentes', 'cantidad', false),
        ]];

    }

    $factura[] = ['titulo' => 'Alquiler contador (€)', 'columnas' => [
        'alquiler_contador' => columnaRejilla('el alquiler del contador', 'importe', false),
    ]];

    if ($servicio === 'luz') {

        $factura[] = ['titulo' => 'Energía reactiva (€)', 'columnas' => [
            'energia_reactiva' => columnaRejilla('la energía reactiva', 'importe', false),
        ]];

        $factura[] = ['titulo' => 'Excesos potencia (€)', 'columnas' => [
            'excesos_potencia' => columnaRejilla('los excesos de potencia', 'importe', false),
        ]];

    }

    // Otros conceptos admite negativos (descuentos).
    $factura[] = ['titulo' => 'Otros conceptos (€)', 'columnas' => [
        'otros_conceptos' => columnaRejilla('otros conceptos', 'importe', false, '', ['negativo' => true]),
    ]];

    $factura[] = ['titulo' => 'IVA', 'columnas' => [
        'iva' => columnaRejilla('el IVA', 'select', false, '', [
            'opciones'    => OPCIONES_IVA,
            'por_defecto' => IVA_POR_DEFECTO,
        ]),
    ]];

    if ($servicio === 'luz') {

        $factura[] = ['titulo' => 'Impuesto eléctrico', 'columnas' => [
            'impuesto_electrico' => columnaRejilla('el impuesto eléctrico', 'select', false, '', [
                'opciones'    => OPCIONES_IMPUESTO_ELECTRICO,
                'por_defecto' => IMPUESTO_ELECTRICO_POR_DEFECTO,
            ]),
        ]];

    }

    $factura[] = ['titulo' => 'Total factura actual (€)', 'columnas' => [
        'total_factura' => columnaRejilla('el total de la factura', 'importe', false),
    ]];

    foreach ($factura as $indice => $grupo) {
        $grupos[] = $grupo + [
            'clase'   => 'grupo-factura' . ($indice === 0 ? ' inicio-factura' : ''),
            'factura' => true,
        ];
    }

    return $grupos;
}


/**
 * Columnas de la rejilla, planas: [columna => info] (ver
 * gruposColumnasTarifa()). Cada una lleva también la 'clase'
 * de su grupo, para pintar las celdas igual que la cabecera.
 */
function columnasRejillaTarifa(string $servicio, string $peaje, string $ambito = 'comercializadoras'): array
{
    $columnas = [];

    foreach (gruposColumnasTarifa($servicio, $peaje, $ambito) as $grupo) {

        $primera = true;

        foreach ($grupo['columnas'] as $columna => $info) {

            // La línea de separación "inicio-factura" solo en
            // la primera columna del primer grupo de factura.
            $clase = $primera ? $grupo['clase'] : str_replace(' inicio-factura', '', $grupo['clase']);

            $columnas[$columna] = $info + ['clase' => $clase, 'factura' => !empty($grupo['factura'])];

            $primera = false;

        }

    }

    return $columnas;
}


/**
 * Pasa un valor escrito a mano o pegado de Excel ("0,1099",
 * "1.234,5", "0,83 €", "21 %") al formato que espera MySQL
 * ("0.1099", "1234.5", "0.83", "21"). Devuelve null si está
 * vacío y false si no es válido para su tipo.
 */
function normalizarValorTarifa(string $valor, array $columna): string|false|null
{
    $valor = str_replace([' ', "\u{00A0}", '€', '%'], '', trim($valor));

    if ($valor === '') {
        return null;
    }

    if ($columna['tipo'] === 'select') {

        foreach (array_keys($columna['opciones']) as $opcion) {

            if (is_numeric(str_replace(',', '.', $valor)) && (float) str_replace(',', '.', $valor) === (float) $opcion) {
                return (string) $opcion;
            }

        }

        return false;

    }

    $negativo = !empty($columna['negativo']) && str_starts_with($valor, '-');

    if ($negativo) {
        $valor = substr($valor, 1);
    }

    // "1.234,56" (miles con punto, como copia Excel en
    // español): se quitan los puntos antes de cambiar la coma.
    if (str_contains($valor, ',')) {
        $valor = str_replace(['.', ','], ['', '.'], $valor);
    }

    if (!preg_match(TIPOS_VALOR_TARIFA[$columna['tipo']]['patron'], $valor)) {
        return false;
    }

    return ($negativo ? '-' : '') . $valor;
}


/**
 * Número guardado ("0.109900", "85.590", "31") tal como se
 * muestra en pantalla: coma decimal y sin ceros sobrantes,
 * pero con al menos $decimalesMinimos decimales ("0,1099",
 * "85,59", "0,83", "31").
 */
function formatearNumero(?string $numero, int $decimalesMinimos = 2): string
{
    if ($numero === null || $numero === '') {
        return '';
    }

    $negativo = str_starts_with($numero, '-');

    [$entero, $decimales] = array_pad(explode('.', ltrim($numero, '-'), 2), 2, '');

    $entero = ltrim($entero, '0') === '' ? '0' : ltrim($entero, '0');
    $decimales = rtrim($decimales, '0');

    if (strlen($decimales) < $decimalesMinimos) {
        $decimales = str_pad($decimales, $decimalesMinimos, '0');
    }

    return ($negativo ? '-' : '') . $entero . ($decimales !== '' ? ',' . $decimales : '');
}


/**
 * Precio guardado tal como se muestra ("0,1099", "1,00").
 */
function formatearPrecio(?string $precio): string
{
    return formatearNumero($precio, 2);
}


/**
 * Valor guardado de una columna tal como se pinta en la
 * rejilla. En los desplegables devuelve la clave de la
 * opción ("5.11269632" en BD -> "5.11269632"; "21.00" -> "21").
 */
function formatearValorTarifa(?string $valor, array $columna): string
{
    if ($columna['tipo'] === 'select') {

        if ($valor === null || $valor === '') {
            return '';
        }

        foreach (array_keys($columna['opciones']) as $opcion) {

            if ((float) $valor === (float) $opcion) {
                return (string) $opcion;
            }

        }

        return '';

    }

    return formatearNumero($valor, TIPOS_VALOR_TARIFA[$columna['tipo']]['decimales_minimos']);
}


/**
 * Recoge los datos de una fila de la rejilla, ya
 * normalizados (como string "0.1099", null si están vacíos o
 * false si no son válidos). Devuelve todas las columnas de
 * `tarifas` y de `datos_factura`: las que no son de este
 * servicio/peaje/ámbito van a null aunque lleguen rellenas.
 */
function recogerDatosTarifa(array $fila, string $servicio, string $peaje, string $ambito): array
{
    $datos = [
        'nombre' => trim((string) ($fila['nombre'] ?? '')),
        'activa' => !empty($fila['activa']) ? 1 : 0,
    ];

    $columnasUsadas = columnasRejillaTarifa($servicio, $peaje, $ambito);

    foreach (array_merge(todasColumnasPrecio(), todasColumnasFactura()) as $columna) {

        $datos[$columna] = isset($columnasUsadas[$columna])
            ? normalizarValorTarifa((string) ($fila[$columna] ?? ''), $columnasUsadas[$columna])
            : null;

    }

    return $datos;
}


/**
 * Valida los datos que devuelve recogerDatosTarifa().
 * Devuelve la lista de errores ['mensaje', 'campo'], vacía
 * si todo es correcto.
 */
function validarDatosTarifa(array $datos, string $servicio, string $peaje, string $ambito): array
{
    $errores = [];

    $longitudNombre = mb_strlen($datos['nombre'], 'UTF-8');

    if ($longitudNombre > 150) {

        $errores[] = ['mensaje' => 'El nombre de la tarifa no puede tener más de 150 caracteres.', 'campo' => 'nombre'];

    }

    foreach (columnasRejillaTarifa($servicio, $peaje, $ambito) as $columna => $info) {

        $valor = $datos[$columna];
        $etiqueta = $info['etiqueta'];

        if ($valor === null) {

            if ($info['obligatorio']) {
                $errores[] = ['mensaje' => "Falta {$etiqueta}.", 'campo' => $columna];
            }

            continue;

        }

        if ($info['tipo'] === 'select') {

            if ($valor === false) {
                $errores[] = ['mensaje' => "Elige {$etiqueta}.", 'campo' => $columna];
            }

            continue;

        }

        $ejemplo = TIPOS_VALOR_TARIFA[$info['tipo']]['ejemplo'];

        if (
            $valor === false
            || ($info['tipo'] === 'precio' && (float) $valor > TARIFA_PRECIO_MAXIMO)
            || ($info['tipo'] === 'dias' && ((int) $valor < 1 || (int) $valor > 366))
        ) {

            $errores[] = ['mensaje' => "Revisa {$etiqueta}: no es un valor válido (usa por ejemplo {$ejemplo}).", 'campo' => $columna];

        }

    }

    return $errores;
}


/**
 * ¿Está la fila de la rejilla completamente en blanco?
 * (sin nombre ni valores escritos; "activa" y los
 * desplegables, que siempre llevan algo, no cuentan). Las
 * filas nuevas en blanco se ignoran al guardar: son los
 * huecos que la rejilla deja preparados para rellenar.
 */
function esFilaTarifaVacia(array $fila): bool
{
    $desplegables = ['iva', 'impuesto_electrico'];

    foreach (array_merge(['nombre'], todasColumnasPrecio(), array_diff(todasColumnasFactura(), $desplegables)) as $campo) {

        if (trim((string) ($fila[$campo] ?? '')) !== '') {
            return false;
        }

    }

    return true;
}


/**
 * Columna de `tarifas` que apunta al titular de la tarifa
 * según el ámbito.
 */
function columnaTitularTarifa(string $ambito): string
{
    return $ambito === 'clientes' ? 'id_cliente' : 'id_comercializadora';
}


/**
 * Titulares (filas de la rejilla) que puede ver el rol
 * actual, indexados por id: ['id', 'nombre', 'detalle'].
 *
 *   comercializadoras -> las que suministran el servicio,
 *                        mismo criterio de visibilidad que el
 *                        listado de Comercializadoras.
 *   clientes          -> mismo criterio que el listado de
 *                        Clientes (SRG todos, NG y EMPRESA
 *                        los que han creado).
 */
function obtenerTitularesTarifas(PDO $pdo, string $ambito, string $servicio): array
{
    if ($ambito === 'clientes') {

        $stmt = $pdo->query("
            SELECT id, CONCAT(nombre, ' ', apellidos) AS nombre, nif AS detalle, creado_por
            FROM clientes
            ORDER BY nombre, apellidos
        ");

        $puedeVer = 'puedeVerCliente';

    } else {

        $campoServicio = $servicio === 'gas' ? 'suministra_gas' : 'suministra_luz';

        $stmt = $pdo->query("
            SELECT id, nombre, '' AS detalle, creado_por
            FROM comercializadoras
            WHERE $campoServicio = 1
            ORDER BY nombre
        ");

        $puedeVer = 'puedeVerComercializadora';

    }

    $titulares = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $titular) {

        if ($puedeVer($titular['creado_por'] !== null ? (int) $titular['creado_por'] : null)) {
            $titulares[(int) $titular['id']] = $titular;
        }

    }

    return $titulares;
}


/**
 * Tarifas de un ámbito/servicio/peaje para los titulares
 * indicados, indexadas por id. Cada una lleva 'id_titular'
 * y, si la rejilla los lleva, sus datos de factura.
 */
function obtenerTarifasRejilla(PDO $pdo, string $ambito, string $servicio, string $peaje, array $idsTitulares): array
{
    if (empty($idsTitulares)) {
        return [];
    }

    $columnaTitular = columnaTitularTarifa($ambito);
    $marcadores = implode(', ', array_fill(0, count($idsTitulares), '?'));

    $columnasFactura = tieneDatosFactura($servicio, $ambito)
        ? ', ' . implode(', ', array_map(fn(string $columna): string => 'f.' . $columna, todasColumnasFactura()))
        : '';

    $joinFactura = tieneDatosFactura($servicio, $ambito)
        ? 'LEFT JOIN datos_factura f ON f.id_tarifa = t.id'
        : '';

    $stmt = $pdo->prepare("
        SELECT t.*, t.$columnaTitular AS id_titular $columnasFactura
        FROM tarifas t
        $joinFactura
        WHERE t.tipo_suministro = ?
            AND t.peaje = ?
            AND t.$columnaTitular IN ($marcadores)
        ORDER BY t.nombre, t.id
    ");

    $stmt->execute(array_merge([$servicio, $peaje], array_values($idsTitulares)));

    $tarifas = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $tarifa) {
        $tarifas[(int) $tarifa['id']] = $tarifa;
    }

    return $tarifas;
}


/**
 * Tarifa (con el nombre de su titular) si existe y el rol
 * actual puede ver su comercializadora o cliente; null si no.
 */
function obtenerTarifaVisible(PDO $pdo, int $idTarifa): ?array
{
    if ($idTarifa <= 0) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT
            t.*,
            COALESCE(c.nombre, CONCAT(cl.nombre, ' ', cl.apellidos)) AS nombre_titular,
            c.creado_por AS comercializadora_creado_por,
            cl.creado_por AS cliente_creado_por
        FROM tarifas t
        LEFT JOIN comercializadoras c ON c.id = t.id_comercializadora
        LEFT JOIN clientes cl ON cl.id = t.id_cliente
        WHERE t.id = ?
    ");

    $stmt->execute([$idTarifa]);

    $tarifa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarifa) {
        return null;
    }

    $puedeVer = $tarifa['id_cliente'] !== null
        ? puedeVerCliente($tarifa['cliente_creado_por'] !== null ? (int) $tarifa['cliente_creado_por'] : null)
        : puedeVerComercializadora($tarifa['comercializadora_creado_por'] !== null ? (int) $tarifa['comercializadora_creado_por'] : null);

    return $puedeVer ? $tarifa : null;
}


/**
 * URL de la página de tarifas para un servicio/peaje.
 * $clienteAbierto: la tabla "Tarifa del cliente" se muestra
 * desplegada encima de la de comercializadoras (cliente=1).
 */
function urlRejillaTarifas(string $servicio, ?string $peaje = null, bool $clienteAbierto = false): string
{
    $parametros = ['servicio' => $servicio];

    if ($peaje !== null) {
        $parametros['peaje'] = $peaje;
    }

    if ($clienteAbierto) {
        $parametros['cliente'] = '1';
    }

    return 'tarifas.php?' . http_build_query($parametros);
}


/**
 * Resultado de guardar la rejilla, para mostrarlo una vez
 * al volver a tarifas.php (POST/redirect/GET):
 *   ['rejilla' => 'ámbito|servicio|peaje', 'mensaje',
 *    'detalle' => líneas de error,
 *    'filas'   => lo enviado (solo si hubo errores, para no
 *                 perder lo escrito),
 *    'errores' => [clave de fila => [campo => mensaje]]]
 */
function establecerResultadoTarifas(array $resultado): void
{
    $_SESSION['tarifas_resultado'] = $resultado;
}

function obtenerResultadoTarifas(): ?array
{
    $resultado = $_SESSION['tarifas_resultado'] ?? null;

    unset($_SESSION['tarifas_resultado']);

    return $resultado;
}

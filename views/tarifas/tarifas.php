<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/tarifas.php';
require_once '../../includes/comparativa.php';


// =====================================================
// QUÉ SE ESTÁ VIENDO
// =====================================================
//
// Flujo de la página:
//   1. Tarifas: se ven las tarifas de las comercializadoras
//      del servicio (luz / gas) y peaje (pestaña) elegidos.
//   2. "Tarifa del cliente": se despliega encima una tabla de
//      una sola fila; en su celda Cliente se busca y elige el
//      cliente, y se rellena o corrige su tarifa actual y su
//      factura.
//   3. En la tabla de comercializadoras se marcan las que se
//      quieren comparar, y "Comparar" muestra el resultado
//      bajo la tabla del cliente.
//
// Parámetros (GET):
//   servicio, peaje -> menú superior y pestaña
//   cliente=1       -> tabla del cliente desplegada
//   id_cliente      -> cliente elegido en el buscador
//   comparar=1      -> mostrar el resultado de la comparativa,
//                      con comp[] (comercializadoras marcadas)
//                      y otros=1 (mantener otros conceptos)
//
// =====================================================

$servicio = in_array($_GET['servicio'] ?? '', SERVICIOS_TARIFA, true) ? $_GET['servicio'] : 'luz';

$peajes = peajesServicio($servicio);
$peaje = in_array($_GET['peaje'] ?? '', $peajes, true) ? $_GET['peaje'] : $peajes[0];

$resultado = obtenerResultadoTarifas();

// Clientes que puede ver el rol actual (para su buscador) y
// el elegido, si lo hay.
$clientes = obtenerTitularesTarifas($pdo, 'clientes', $servicio);
$idCliente = (int) ($_GET['id_cliente'] ?? 0);
$clienteElegido = $clientes[$idCliente] ?? null;

if ($clienteElegido === null) {
    $idCliente = 0;
}

// Se abre la tabla del cliente si se pide, si hay un cliente
// elegido o si se vuelve de guardarla (para ver el mensaje).
$clienteAbierto = ($_GET['cliente'] ?? '') === '1'
    || $clienteElegido !== null
    || str_starts_with($resultado['rejilla'] ?? '', 'clientes|');

$etiquetaServicio = $servicio === 'gas' ? 'gas' : 'luz';


/**
 * Datos de una de las dos tablas (ámbito 'clientes' o
 * 'comercializadoras') listos para pintarla: titulares,
 * columnas y filas agrupadas por titular.
 *
 * En la de clientes solo entra el cliente elegido en el
 * buscador ($idCliente; ninguno si es 0) y con una sola fila.
 *
 * Si $resultado es de esta tabla y trae filas (guardado con
 * errores), se pintan tal como se enviaron para no perder lo
 * escrito; si no, desde la base de datos. Cada titular sin
 * tarifa recibe una fila en blanco lista para rellenar.
 */
function prepararRejillaTarifas(PDO $pdo, string $ambito, string $servicio, string $peaje, ?array $resultado, int $idCliente = 0): array
{
    $gruposColumnas = gruposColumnasTarifa($servicio, $peaje, $ambito);
    $columnas = columnasRejillaTarifa($servicio, $peaje, $ambito);
    $esClientes = $ambito === 'clientes';

    // Dos filas de cabecera si algún grupo tiene subcolumnas
    // (Energía P1..Pn); si no, basta con una.
    $cabeceraDoble = false;

    foreach ($gruposColumnas as $grupo) {
        if (count($grupo['columnas']) > 1) {
            $cabeceraDoble = true;
        }
    }

    $titulares = obtenerTitularesTarifas($pdo, $ambito, $servicio);

    if ($esClientes) {
        $titulares = isset($titulares[$idCliente]) ? [$idCliente => $titulares[$idCliente]] : [];
    }

    if ($resultado !== null && ($resultado['rejilla'] ?? '') !== $ambito . '|' . $servicio . '|' . $peaje) {
        $resultado = null;
    }

    $filasPorTitular = array_fill_keys(array_keys($titulares), []);

    if (!empty($resultado['filas'])) {

        foreach ($resultado['filas'] as $clave => $fila) {

            $idTitular = (int) ($fila['id_titular'] ?? 0);

            if (isset($filasPorTitular[$idTitular])) {
                $filasPorTitular[$idTitular][] = ['clave' => (string) $clave] + $fila;
            }

        }

    } else {

        foreach (obtenerTarifasRejilla($pdo, $ambito, $servicio, $peaje, array_keys($titulares)) as $tarifa) {

            $fila = [
                'clave'      => 't' . $tarifa['id'],
                'id'         => $tarifa['id'],
                'id_titular' => $tarifa['id_titular'],
                'nombre'     => $tarifa['nombre'],
                'activa'     => $tarifa['activa'],
            ];

            foreach ($columnas as $columna => $info) {
                $fila[$columna] = formatearValorTarifa($tarifa[$columna] ?? null, $info);
            }

            $filasPorTitular[(int) $tarifa['id_titular']][] = $fila;

        }

    }

    foreach ($filasPorTitular as $idTitular => $filas) {

        if (empty($filas)) {

            $filasPorTitular[$idTitular][] = [
                'clave'      => 'n' . $idTitular,
                'id'         => '',
                'id_titular' => $idTitular,
                'activa'     => '1',
            ];

        } elseif ($esClientes) {

            // Una sola fila por cliente: su tarifa actual.
            $filasPorTitular[$idTitular] = array_slice($filas, 0, 1);

        }

    }

    return [
        'ambito'          => $ambito,
        'es_clientes'     => $esClientes,
        'grupos'          => $gruposColumnas,
        'columnas'        => $columnas,
        'cabecera_doble'  => $cabeceraDoble,
        'titulares'       => $titulares,
        'filas'           => $filasPorTitular,
        'resultado'       => $resultado,
        'errores'         => $resultado['errores'] ?? [],
    ];
}

$rejillaClientes = prepararRejillaTarifas($pdo, 'clientes', $servicio, $peaje, $resultado, $idCliente);
$rejillaComercializadoras = prepararRejillaTarifas($pdo, 'comercializadoras', $servicio, $peaje, $resultado);


// =====================================================
// COMPARATIVA (AL PULSAR "COMPARAR")
// =====================================================

$comparar = ($_GET['comparar'] ?? '') === '1' && $clienteElegido !== null;
$incluirOtros = ($_GET['otros'] ?? '') === '1';

// Comercializadoras marcadas para comparar: las de comp[] al
// volver de Comparar; si no, todas.
$compararMarcadas = $comparar
    ? array_values(array_intersect(
        array_map('intval', (array) ($_GET['comp'] ?? [])),
        array_keys($rejillaComercializadoras['titulares'])
    ))
    : array_keys($rejillaComercializadoras['titulares']);

$comparativa = null;
$avisoComparar = null;
$tarifaCliente = null;

if ($comparar) {

    // Mismo cálculo que la exportación a PDF
    // (exportar_comparativa_pdf.php).
    $datosComparativa = prepararComparativaCliente($pdo, $servicio, $peaje, $idCliente, $compararMarcadas, $incluirOtros);

    $tarifaCliente = $datosComparativa['tarifa_cliente'];
    $avisoComparar = $datosComparativa['aviso'];
    $comparativa = $datosComparativa['comparativa'];

}

// Enlace "Exportar PDF" del resultado: los mismos parámetros
// que la comparativa en pantalla.
$urlExportarPdf = 'exportar_comparativa_pdf.php?' . http_build_query([
    'servicio'   => $servicio,
    'peaje'      => $peaje,
    'id_cliente' => $idCliente,
    'comp'       => $compararMarcadas,
    'otros'      => $incluirOtros ? '1' : '0',
]);


/**
 * Buscador de cliente (celda "Cliente" de su tabla): elige
 * el cliente de la lista #listaClientesTarifa (ver
 * js/tarifas.js, que al elegirlo recarga con su id_cliente).
 */
function pintarBuscadorCliente(string $valor): void
{
    ?>
    <input type="search" class="buscador-cliente" list="listaClientesTarifa"
        value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="Buscar cliente…" aria-label="Buscar cliente por nombre o NIF" autocomplete="off">
    <?php
}


/**
 * Pinta una fila de la rejilla. $fila trae los valores tal
 * como se muestran ("0,1099"); $primera indica si es la
 * primera fila de su titular (la que lleva el nombre, la
 * casilla de comparar y el botón de añadir otra tarifa).
 * $columnas: ver columnasRejillaTarifa().
 *
 * En la tabla de clientes, la celda del titular es el
 * buscador de cliente (y no hay "+": una sola fila).
 */
function pintarFilaTarifa(array $fila, array $titular, bool $primera, array $columnas, bool $esClientes, string $placeholderNombre, array $errores, bool $compararMarcada = true): void
{
    $clave = $fila['clave'];
    $prefijo = 'filas[' . $clave . ']';
    $e = fn(string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');

    // Una celda: texto, o desplegable en IVA / impuesto
    // eléctrico (que, vacío, toma su valor por defecto).
    $celda = function (string $campo, array $info, string $placeholder) use ($fila, $prefijo, $errores, $e): void {

        $error = $errores[$campo] ?? null;
        $valor = (string) ($fila[$campo] ?? '');
        $nombreCampo = $e($prefijo . '[' . $campo . ']');

        echo '<td class="' . $e(trim($info['clase'] . ($error !== null ? ' celda-error' : ''))) . '"'
            . ($error !== null ? ' title="' . $e($error) . '"' : '') . '>';

        if ($info['tipo'] === 'select') {

            $elegido = $valor !== '' ? $valor : $info['por_defecto'];

            echo '<select name="' . $nombreCampo . '" data-campo="' . $e($campo) . '">';

            foreach ($info['opciones'] as $opcion => $texto) {
                echo '<option value="' . $e((string) $opcion) . '"' . ((string) $opcion === $elegido ? ' selected' : '') . '>'
                    . $e($texto) . '</option>';
            }

            echo '</select>';

        } else {

            echo '<input type="text" name="' . $nombreCampo . '"'
                . ' value="' . $e($valor) . '"'
                . ' placeholder="' . $e($placeholder) . '"'
                . ($info['tipo'] === 'nombre' ? ' maxlength="150"' : ' inputmode="decimal"')
                // Para validar la celda en el navegador (js/tarifas.js)
                // con las mismas reglas que validarDatosTarifa().
                . ' data-tipo="' . $e($info['tipo']) . '"'
                . ' data-etiqueta="' . $e($info['etiqueta'] ?? 'el nombre') . '"'
                . (!empty($info['negativo']) ? ' data-negativo="1"' : '')
                . ' data-campo="' . $e($campo) . '" autocomplete="off">';

        }

        echo '</td>';

    };

    $textoAnadir = 'Añadir otra tarifa a ' . $titular['nombre'];

    ?>
    <tr class="tarifa-fila<?= $primera ? '' : ' tarifa-fila-extra' ?>"
        data-clave="<?= $e($clave) ?>"
        data-id="<?= $e((string) ($fila['id'] ?? '')) ?>"
        data-titular="<?= $e((string) $fila['id_titular']) ?>">

        <th scope="row" class="col-titular">

            <?php if ($esClientes): ?>

                <?php pintarBuscadorCliente($titular['nombre']); ?>

                <?php if ($titular['detalle'] !== ''): ?>
                    <span class="detalle-titular"><?= $e($titular['detalle']) ?></span>
                <?php endif; ?>

            <?php else: ?>

                <input type="checkbox" class="comparar-casilla" data-comparar-comercializadora
                    value="<?= $e((string) $fila['id_titular']) ?>"
                    title="Incluir en la comparativa" aria-label="Incluir <?= $e($titular['nombre']) ?> en la comparativa"
                    <?= $compararMarcada ? 'checked' : '' ?>>

                <span class="nombre-titular"><?= $e($titular['nombre']) ?></span>

                <?php if ($titular['detalle'] !== ''): ?>
                    <span class="detalle-titular"><?= $e($titular['detalle']) ?></span>
                <?php endif; ?>

                <button type="button" class="tarifa-nueva" data-nueva-tarifa title="<?= $e($textoAnadir) ?>"
                    aria-label="<?= $e($textoAnadir) ?>">+</button>

            <?php endif; ?>

            <input type="hidden" name="<?= $e($prefijo) ?>[id]" value="<?= $e((string) ($fila['id'] ?? '')) ?>">
            <input type="hidden" name="<?= $e($prefijo) ?>[id_titular]" value="<?= $e((string) $fila['id_titular']) ?>">

        </th>

        <?php $celda('nombre', ['tipo' => 'nombre', 'clase' => 'col-nombre'], $placeholderNombre); ?>

        <?php foreach ($columnas as $columna => $info): ?>
            <?php $celda($columna, ['clase' => 'col-precio ' . $info['clase']] + $info, ''); ?>
        <?php endforeach; ?>

        <?php if (!$esClientes): ?>
            <td class="col-activa">
                <input type="hidden" name="<?= $e($prefijo) ?>[activa]" value="0">
                <input type="checkbox" name="<?= $e($prefijo) ?>[activa]" value="1" data-campo="activa"
                    title="Activa: se usa en las comparativas"
                    <?= !empty($fila['activa']) ? 'checked' : '' ?>>
            </td>
        <?php endif; ?>

        <td class="col-acciones">
            <button type="button" class="table-action-button icon-action-button danger" data-eliminar-fila
                title="Eliminar tarifa">
                <i class="bi bi-trash3"></i>
            </button>
        </td>

    </tr>
    <?php
}


/**
 * Pinta una tabla completa (título, buscar, Guardar, mensaje
 * del último guardado y rejilla) dentro de su propio
 * formulario: cada tabla se guarda por separado.
 */
function pintarRejillaTarifas(array $rejilla, string $servicio, string $peaje, string $etiquetaServicio, bool $clienteAbierto, int $idCliente, array $clientes, array $compararMarcadas): void
{
    $e = fn(string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');

    $esClientes = $rejilla['es_clientes'];
    $ambito = $rejilla['ambito'];
    $resultado = $rejilla['resultado'];
    $hayErrores = !empty($rejilla['errores']);
    $filasCabecera = $rejilla['cabecera_doble'] ? 2 : 1;
    $placeholderNombre = $esClientes ? 'Comercializadora / tarifa' : 'Nombre tarifa';

    // En la de clientes, sin cliente elegido solo hay buscador:
    // no hay nada que guardar todavía.
    $sinCliente = $esClientes && empty($rejilla['titulares']);

    // Columnas de la tabla (para el colspan de la fila del
    // buscador): titular, tarifa, precios/factura, acciones.
    $totalColumnas = 3 + count($rejilla['columnas']) + ($esClientes ? 0 : 1);

    ?>
    <?php if ($resultado !== null): ?>

        <div class="form-info <?= $hayErrores ? 'form-error-general' : 'registration-success' ?> tarifas-mensaje"
            role="<?= $hayErrores ? 'alert' : 'status' ?>" style="display:block;">
            <p><?= $e($resultado['mensaje']) ?></p>
            <?php if (!empty($resultado['detalle'])): ?>
                <ul>
                    <?php foreach ($resultado['detalle'] as $linea): ?>
                        <li><?= $e($linea) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <form action="guardar_tarifas.php" method="POST" class="tarifas-form" novalidate
        data-ambito="<?= $e($ambito) ?>"
        data-hay-cambios="<?= $hayErrores ? '1' : '0' ?>">

        <input type="hidden" name="vista" value="<?= $e($ambito) ?>">
        <input type="hidden" name="servicio" value="<?= $e($servicio) ?>">
        <input type="hidden" name="peaje" value="<?= $e($peaje) ?>">
        <input type="hidden" name="cliente_abierto" value="<?= $clienteAbierto ? '1' : '0' ?>">
        <input type="hidden" name="id_cliente" value="<?= $idCliente ?>">


        <!-- TÍTULO + BUSCAR + GUARDAR -->

        <div class="tarifas-barra">

            <div class="tarifas-titulo">
                <h2>
                    <?= $esClientes ? 'Tarifa del cliente' : 'Tarifas de las comercializadoras' ?>
                    <span>(<?= $e($etiquetaServicio . ' · ' . $peaje) ?>)</span>
                </h2>
                <p>
                    <?= $esClientes
                        ? 'Busca el cliente y rellena o corrige su tarifa actual y su factura.'
                        : 'Precios que ofrece cada comercializadora. Marca las que quieras usar al comparar.' ?>
                </p>
            </div>

            <?php if (!$sinCliente): ?>

                <div class="tarifas-acciones">

                    <?php if (!$esClientes): ?>
                        <input type="search" class="tarifas-buscar"
                            placeholder="Buscar comercializadora" aria-label="Buscar comercializadora">
                    <?php endif; ?>

                    <span class="tarifas-estado" aria-live="polite">
                        <?= $hayErrores ? 'Hay errores sin guardar' : '' ?>
                    </span>

                    <button type="submit" class="config-save-button">
                        Guardar
                    </button>

                </div>

            <?php endif; ?>

        </div>


        <?php if ($esClientes): ?>

            <!-- Opciones del buscador de cliente: "Nombre Apellidos · NIF" -->
            <datalist id="listaClientesTarifa">
                <?php foreach ($clientes as $idOpcion => $opcion): ?>
                    <option value="<?= $e($opcion['nombre'] . ' · ' . $opcion['detalle']) ?>" data-id="<?= (int) $idOpcion ?>"></option>
                <?php endforeach; ?>
            </datalist>

        <?php endif; ?>


        <?php if ($esClientes && empty($clientes)): ?>

            <div class="form-info">
                <p>
                    Todavía no hay clientes.
                    <a href="../clientes/crear_cliente.php">Añade uno</a>
                    para introducir su tarifa actual.
                </p>
            </div>

        <?php elseif (!$esClientes && empty($rejilla['titulares'])): ?>

            <div class="form-info">
                <p>
                    No hay comercializadoras que suministren <?= $e($etiquetaServicio) ?>.
                    <a href="../comercializadoras/comercializadoras.php">Añade o edita una</a>
                    para poder introducir sus tarifas.
                </p>
            </div>

        <?php else: ?>

            <div class="tarifas-rejilla-contenedor">

                <table class="tarifas-rejilla">

                    <thead>

                        <tr>
                            <th rowspan="<?= $filasCabecera ?>" class="col-titular">
                                <?php if ($esClientes): ?>
                                    Cliente
                                <?php else: ?>
                                    <label class="comparar-todas" title="Marcar o desmarcar todas para comparar">
                                        <input type="checkbox" class="comparar-casilla" data-comparar-todas
                                            <?= count($compararMarcadas) === count($rejilla['titulares']) ? 'checked' : '' ?>>
                                        Comercializadora
                                    </label>
                                <?php endif; ?>
                            </th>
                            <th rowspan="<?= $filasCabecera ?>" class="col-nombre">
                                <?= $esClientes ? 'Tarifa actual' : 'Tarifa' ?>
                            </th>
                            <?php foreach ($rejilla['grupos'] as $grupo): ?>
                                <?php if (count($grupo['columnas']) > 1): ?>
                                    <th colspan="<?= count($grupo['columnas']) ?>" class="<?= $e($grupo['clase']) ?>">
                                        <?= $e($grupo['titulo']) ?>
                                    </th>
                                <?php else: ?>
                                    <th rowspan="<?= $filasCabecera ?>" class="col-precio <?= $e($grupo['clase']) ?>">
                                        <?= $e($grupo['titulo']) ?>
                                    </th>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (!$esClientes): ?>
                                <th rowspan="<?= $filasCabecera ?>" class="col-activa">Activa</th>
                            <?php endif; ?>
                            <th rowspan="<?= $filasCabecera ?>" class="col-acciones" aria-label="Acciones"></th>
                        </tr>

                        <?php if ($rejilla['cabecera_doble']): ?>
                            <tr>
                                <?php foreach ($rejilla['grupos'] as $grupo): ?>
                                    <?php if (count($grupo['columnas']) > 1): ?>
                                        <?php foreach (array_keys($grupo['columnas']) as $columna): ?>
                                            <th class="<?= $e($rejilla['columnas'][$columna]['clase']) ?>">
                                                <?= $e($rejilla['columnas'][$columna]['cabecera']) ?>
                                            </th>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>

                    </thead>

                    <?php if ($sinCliente): ?>

                        <!-- Sin cliente elegido: solo el buscador -->
                        <tbody>
                            <tr class="tarifa-fila-buscador">
                                <th scope="row" class="col-titular">
                                    <?php pintarBuscadorCliente(''); ?>
                                </th>
                                <td colspan="<?= $totalColumnas - 1 ?>" class="tarifa-buscador-ayuda">
                                    ← Busca y elige un cliente para ver o introducir su tarifa actual.
                                </td>
                            </tr>
                        </tbody>

                    <?php else: ?>

                        <tbody class="tarifas-body">

                            <?php foreach ($rejilla['filas'] as $idTitular => $filas): ?>
                                <?php foreach ($filas as $indice => $fila): ?>
                                    <?php
                                    pintarFilaTarifa(
                                        $fila,
                                        $rejilla['titulares'][$idTitular],
                                        $indice === 0,
                                        $rejilla['columnas'],
                                        $esClientes,
                                        $placeholderNombre,
                                        $rejilla['errores'][$fila['clave']] ?? [],
                                        in_array((int) $idTitular, $compararMarcadas, true)
                                    );
                                    ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                        </tbody>

                    <?php endif; ?>

                </table>

            </div>


            <?php if (!$esClientes): ?>

                <!-- Plantilla de fila nueva (botón +). js/tarifas.js
                     cambia __CLAVE__ y __TITULAR__ por los valores
                     reales y copia el nombre del titular. -->

                <template class="plantilla-fila-tarifa">
                    <?php
                    pintarFilaTarifa(
                        ['clave' => '__CLAVE__', 'id' => '', 'id_titular' => '__TITULAR__', 'activa' => '1'],
                        ['nombre' => '', 'detalle' => ''],
                        false,
                        $rejilla['columnas'],
                        false,
                        $placeholderNombre,
                        []
                    );
                    ?>
                </template>

            <?php endif; ?>

        <?php endif; ?>

    </form>
    <?php
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tarifas - Comparador Eléctrico</title>

    <link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <?php include '../../templates/header.php'; ?>

    <div class="app-container">

        <?php include '../../templates/sidebar.php'; ?>

        <main class="main-content">

            <!-- =================================================
                 CABECERA
            ================================================== -->

            <div class="page-header">

                <div>

                    <h1>Tarifas</h1>

                    <p>
                        Tarifas de las comercializadoras. Con "Tarifa del cliente" puedes compararlas
                        con lo que paga hoy un cliente.
                    </p>

                </div>

            </div>


            <!-- =================================================
                 MENÚ: LUZ / GAS + BOTÓN "TARIFA DEL CLIENTE"
                 =================================================
                 Los enlaces llevan data-enlace-tarifas: js/tarifas.js
                 les añade o quita cliente=1 al abrir/cerrar la
                 tabla del cliente, para que siga igual al navegar.
            ================================================== -->

            <div class="tarifas-menu">

                <nav class="tarifas-servicios" aria-label="Servicio">
                    <?php foreach (['luz' => '⚡ Tarifas de luz', 'gas' => '🔥 Tarifas de gas'] as $opcionServicio => $textoServicio): ?>
                        <a href="<?= htmlspecialchars(urlRejillaTarifas($opcionServicio, null, $clienteAbierto, $idCliente), ENT_QUOTES, 'UTF-8') ?>"
                            data-enlace-tarifas
                            class="tarifas-servicio<?= $opcionServicio === $servicio ? ' activo' : '' ?>"
                            <?= $opcionServicio === $servicio ? 'aria-current="page"' : '' ?>>
                            <?= $textoServicio ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <button type="button" class="tarifas-cliente-toggle<?= $clienteAbierto ? ' activo' : '' ?>"
                    id="btnTarifaCliente" aria-controls="panelTarifaCliente"
                    aria-expanded="<?= $clienteAbierto ? 'true' : 'false' ?>">
                    <i class="bi bi-person"></i>
                    Tarifa del cliente
                </button>

            </div>


            <!-- =================================================
                 PESTAÑAS DE PEAJE (COMUNES A LAS DOS TABLAS)
            ================================================== -->

            <div class="tarifas-peajes">

                <nav class="tarifas-tabs" aria-label="Tipo de tarifa">
                    <?php foreach ($peajes as $opcionPeaje): ?>
                        <a href="<?= htmlspecialchars(urlRejillaTarifas($servicio, $opcionPeaje, $clienteAbierto, $idCliente), ENT_QUOTES, 'UTF-8') ?>"
                            data-enlace-tarifas
                            class="tarifas-tab<?= $opcionPeaje === $peaje ? ' activa' : '' ?>"
                            <?= $servicio === 'gas' ? 'title="' . htmlspecialchars(PEAJES_GAS[$opcionPeaje], ENT_QUOTES, 'UTF-8') . '"' : '' ?>
                            <?= $opcionPeaje === $peaje ? 'aria-current="page"' : '' ?>>
                            <?= htmlspecialchars($opcionPeaje, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <p class="tarifas-ayuda">
                    <?php if ($servicio === 'gas'): ?>
                        <?= htmlspecialchars($peaje . ': ' . PEAJES_GAS[$peaje], ENT_QUOTES, 'UTF-8') ?> ·
                    <?php endif; ?>
                    <kbd>Enter</kbd> baja a la fila siguiente · puedes pegar varias celdas copiadas de Excel ·
                    las filas que dejes en blanco no se guardan · <strong>+</strong> añade otra tarifa
                </p>

            </div>


            <!-- =================================================
                 TARIFA DEL CLIENTE (DESPLEGABLE) + COMPARAR
            ================================================== -->

            <section class="panel tarifas-panel tarifas-panel-cliente" id="panelTarifaCliente"
                <?= $clienteAbierto ? '' : 'hidden' ?>>

                <?php pintarRejillaTarifas($rejillaClientes, $servicio, $peaje, $etiquetaServicio, $clienteAbierto, $idCliente, $clientes, $compararMarcadas); ?>

                <?php if ($clienteElegido !== null): ?>

                    <!-- Comparar: con el cliente de arriba y las
                         comercializadoras marcadas en la tabla de
                         abajo (ver js/tarifas.js, que monta la URL
                         con comparar=1, comp[] y otros). -->

                    <div class="tarifas-comparar">

                        <p class="tarifas-comparar-aviso" id="compararAviso" role="alert" hidden></p>

                        <label class="comparar-opcion">
                            <input type="checkbox" id="compararOtros" <?= $incluirOtros ? 'checked' : '' ?>>
                            Mantener sus "otros conceptos" en las ofertas
                        </label>

                        <button type="button" class="config-save-button tarifas-comparar-boton" id="btnComparar">
                            <i class="bi bi-bar-chart"></i>
                            Comparar
                        </button>

                    </div>

                    <?php if ($comparar): ?>
                        <?php
                        $nombreCliente = $clienteElegido['nombre'];
                        include 'resultado_comparativa.php';
                        ?>
                    <?php endif; ?>

                <?php endif; ?>

            </section>


            <!-- =================================================
                 TARIFAS DE LAS COMERCIALIZADORAS
            ================================================== -->

            <section class="panel tarifas-panel">

                <?php pintarRejillaTarifas($rejillaComercializadoras, $servicio, $peaje, $etiquetaServicio, $clienteAbierto, $idCliente, $clientes, $compararMarcadas); ?>

            </section>

        </main>

    </div>

    <?php include '../../templates/footer.php'; ?>


    <!-- =====================================================
         MODAL CONFIRMAR ELIMINACIÓN (COMÚN A LAS DOS TABLAS)
    ====================================================== -->

    <div id="modalEliminarTarifa" class="modal-overlay" style="display: none;">

        <div class="modal-confirmacion deletion-modal">

            <div class="modal-icon" aria-hidden="true"><i class="bi bi-trash3"></i></div>

            <h2>Eliminar tarifa</h2>

            <p>
                ¿Estás seguro de que quieres eliminar la tarifa <strong id="nombreTarifaEliminar"></strong>?
            </p>

            <p class="modal-warning">
                Esta acción no se puede deshacer.
                <span id="pistaEliminarTarifa">
                    Si solo quieres que deje de usarse en las comparativas, desmarca "Activa" y guarda.
                </span>
            </p>

            <div class="modal-actions">

                <button type="button" class="modal-button modal-button-cancel" id="btnCancelarEliminarTarifa">
                    Cancelar
                </button>

                <button type="button" class="modal-button modal-button-delete" id="btnConfirmarEliminarTarifa">
                    Eliminar tarifa
                </button>

            </div>

        </div>

    </div>


    <script src="../../js/notificacion-eliminacion.js"></script>
    <script src="../../js/tarifas.js"></script>

</body>

</html>

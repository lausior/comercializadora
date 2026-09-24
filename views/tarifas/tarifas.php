<?php

session_start();

require_once '../../config/permisos.php';
requerirPermiso('tarifas');

require_once '../../config/database.php';
require_once '../../includes/tarifas.php';


// =====================================================
// QUÉ SE ESTÁ VIENDO
// =====================================================
//
//   servicio -> luz / gas (menú superior)
//   peaje    -> pestaña: cada peaje tiene sus propias
//               columnas, así que no caben en la misma tabla.
//               Es la misma para las dos tablas: se compara
//               siempre dentro del mismo peaje.
//   cliente  -> "1" si la tabla "Tarifa del cliente" está
//               desplegada encima de la de comercializadoras
//               (botón "Tarifa del cliente", ver js/tarifas.js)
//
// =====================================================

$servicio = in_array($_GET['servicio'] ?? '', SERVICIOS_TARIFA, true) ? $_GET['servicio'] : 'luz';

$peajes = peajesServicio($servicio);
$peaje = in_array($_GET['peaje'] ?? '', $peajes, true) ? $_GET['peaje'] : $peajes[0];

$resultado = obtenerResultadoTarifas();

// Si se vuelve de guardar la tabla del cliente, se abre
// aunque la URL no lo diga, para ver el mensaje.
$clienteAbierto = ($_GET['cliente'] ?? '') === '1'
    || str_starts_with($resultado['rejilla'] ?? '', 'clientes|');

$etiquetaServicio = $servicio === 'gas' ? 'gas' : 'luz';


/**
 * Datos de una de las dos tablas (ámbito 'clientes' o
 * 'comercializadoras') listos para pintarla: titulares,
 * columnas y filas agrupadas por titular.
 *
 * Si $resultado es de esta tabla y trae filas (guardado con
 * errores), se pintan tal como se enviaron para no perder lo
 * escrito; si no, desde la base de datos. Cada titular sin
 * tarifa recibe una fila en blanco lista para rellenar.
 */
function prepararRejillaTarifas(PDO $pdo, string $ambito, string $servicio, string $peaje, ?array $resultado): array
{
    $gruposColumnas = gruposColumnasTarifa($servicio, $peaje, $ambito);
    $columnas = columnasRejillaTarifa($servicio, $peaje, $ambito);

    // Dos filas de cabecera si algún grupo tiene subcolumnas
    // (Energía P1..Pn); si no, basta con una.
    $cabeceraDoble = false;

    foreach ($gruposColumnas as $grupo) {
        if (count($grupo['columnas']) > 1) {
            $cabeceraDoble = true;
        }
    }

    $titulares = obtenerTitularesTarifas($pdo, $ambito, $servicio);

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
        }

    }

    return [
        'ambito'          => $ambito,
        'es_clientes'     => $ambito === 'clientes',
        'grupos'          => $gruposColumnas,
        'columnas'        => $columnas,
        'cabecera_doble'  => $cabeceraDoble,
        'titulares'       => $titulares,
        'filas'           => $filasPorTitular,
        'resultado'       => $resultado,
        'errores'         => $resultado['errores'] ?? [],
    ];
}

$rejillaClientes = prepararRejillaTarifas($pdo, 'clientes', $servicio, $peaje, $resultado);
$rejillaComercializadoras = prepararRejillaTarifas($pdo, 'comercializadoras', $servicio, $peaje, $resultado);


/**
 * Pinta una fila de la rejilla. $fila trae los valores tal
 * como se muestran ("0,1099"); $primera indica si es la
 * primera fila de su titular (la que lleva el nombre y el
 * botón de añadir otra tarifa). $columnas: ver
 * columnasRejillaTarifa().
 */
function pintarFilaTarifa(array $fila, array $titular, bool $primera, array $columnas, bool $conActiva, string $placeholderNombre, array $errores): void
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
            <span class="nombre-titular"><?= $e($titular['nombre']) ?></span>
            <?php if ($titular['detalle'] !== ''): ?>
                <span class="detalle-titular"><?= $e($titular['detalle']) ?></span>
            <?php endif; ?>
            <button type="button" class="tarifa-nueva" data-nueva-tarifa title="<?= $e($textoAnadir) ?>"
                aria-label="<?= $e($textoAnadir) ?>">+</button>
            <input type="hidden" name="<?= $e($prefijo) ?>[id]" value="<?= $e((string) ($fila['id'] ?? '')) ?>">
            <input type="hidden" name="<?= $e($prefijo) ?>[id_titular]" value="<?= $e((string) $fila['id_titular']) ?>">
        </th>

        <?php $celda('nombre', ['tipo' => 'nombre', 'clase' => 'col-nombre'], $placeholderNombre); ?>

        <?php foreach ($columnas as $columna => $info): ?>
            <?php $celda($columna, ['clase' => 'col-precio ' . $info['clase']] + $info, ''); ?>
        <?php endforeach; ?>

        <?php if ($conActiva): ?>
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
function pintarRejillaTarifas(array $rejilla, string $servicio, string $peaje, string $etiquetaServicio, bool $clienteAbierto): void
{
    $e = fn(string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');

    $esClientes = $rejilla['es_clientes'];
    $ambito = $rejilla['ambito'];
    $resultado = $rejilla['resultado'];
    $hayErrores = !empty($rejilla['errores']);
    $filasCabecera = $rejilla['cabecera_doble'] ? 2 : 1;
    $placeholderNombre = $esClientes ? 'Comercializadora / tarifa' : 'Nombre tarifa';
    $textoBuscar = $esClientes ? 'Buscar cliente o NIF' : 'Buscar comercializadora';

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


        <!-- TÍTULO + BUSCAR + GUARDAR -->

        <div class="tarifas-barra">

            <div class="tarifas-titulo">
                <h2>
                    <?= $esClientes ? 'Tarifa actual de los clientes' : 'Tarifas de las comercializadoras' ?>
                    <span>(<?= $e($etiquetaServicio . ' · ' . $peaje) ?>)</span>
                </h2>
                <p>
                    <?= $esClientes
                        ? 'Precios y factura que cada cliente paga hoy.'
                        : 'Precios que ofrece cada comercializadora.' ?>
                </p>
            </div>

            <div class="tarifas-acciones">

                <input type="search" class="tarifas-buscar"
                    placeholder="<?= $e($textoBuscar) ?>" aria-label="<?= $e($textoBuscar) ?>">

                <span class="tarifas-estado" aria-live="polite">
                    <?= $hayErrores ? 'Hay errores sin guardar' : '' ?>
                </span>

                <button type="submit" class="config-save-button">
                    Guardar
                </button>

            </div>

        </div>


        <?php if (empty($rejilla['titulares'])): ?>

            <div class="form-info">
                <p>
                    <?php if ($esClientes): ?>
                        Todavía no hay clientes.
                        <a href="../clientes/crear_cliente.php">Añade uno</a>
                        para introducir su tarifa actual.
                    <?php else: ?>
                        No hay comercializadoras que suministren <?= $e($etiquetaServicio) ?>.
                        <a href="../comercializadoras/comercializadoras.php">Añade o edita una</a>
                        para poder introducir sus tarifas.
                    <?php endif; ?>
                </p>
            </div>

        <?php else: ?>

            <div class="tarifas-rejilla-contenedor">

                <table class="tarifas-rejilla">

                    <thead>

                        <tr>
                            <th rowspan="<?= $filasCabecera ?>" class="col-titular">
                                <?= $esClientes ? 'Cliente' : 'Comercializadora' ?>
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

                    <tbody class="tarifas-body">

                        <?php foreach ($rejilla['filas'] as $idTitular => $filas): ?>
                            <?php foreach ($filas as $indice => $fila): ?>
                                <?php
                                pintarFilaTarifa(
                                    $fila,
                                    $rejilla['titulares'][$idTitular],
                                    $indice === 0,
                                    $rejilla['columnas'],
                                    !$esClientes,
                                    $placeholderNombre,
                                    $rejilla['errores'][$fila['clave']] ?? []
                                );
                                ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


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
                    !$esClientes,
                    $placeholderNombre,
                    []
                );
                ?>
            </template>

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
                        Rellena o cambia las celdas y pulsa Guardar.
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
                        <a href="<?= htmlspecialchars(urlRejillaTarifas($opcionServicio, null, $clienteAbierto), ENT_QUOTES, 'UTF-8') ?>"
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
                        <a href="<?= htmlspecialchars(urlRejillaTarifas($servicio, $opcionPeaje, $clienteAbierto), ENT_QUOTES, 'UTF-8') ?>"
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

                <?php pintarRejillaTarifas($rejillaClientes, $servicio, $peaje, $etiquetaServicio, $clienteAbierto); ?>

                <div class="tarifas-comparar">

                    <p class="form-info tarifas-comparar-aviso" id="compararAviso" role="status" hidden>
                        La comparativa todavía está por construir: aquí se calculará la factura de cada
                        cliente con las tarifas activas de las comercializadoras de este peaje.
                    </p>

                    <button type="button" class="config-save-button tarifas-comparar-boton" id="btnComparar">
                        <i class="bi bi-bar-chart"></i>
                        Comparar
                    </button>

                </div>

            </section>


            <!-- =================================================
                 TARIFAS DE LAS COMERCIALIZADORAS
            ================================================== -->

            <section class="panel tarifas-panel">

                <?php pintarRejillaTarifas($rejillaComercializadoras, $servicio, $peaje, $etiquetaServicio, $clienteAbierto); ?>

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

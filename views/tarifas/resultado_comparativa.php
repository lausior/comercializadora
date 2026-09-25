<?php

/**
 * =====================================================
 * RESULTADO DE LA COMPARATIVA
 * =====================================================
 *
 * Se incluye desde tarifas.php, bajo la tabla "Tarifa del
 * cliente", al pulsar Comparar. Espera definidas:
 *   $comparativa    -> compararFacturaCliente() (includes/
 *                      comparativa.php), o null si no se ha
 *                      podido comparar
 *   $avisoComparar  -> texto a mostrar en lugar del resultado
 *                      (sin tarifa guardada, ninguna
 *                      comercializadora marcada...), o null
 *   $tarifaCliente  -> su tarifa actual con los datos de su
 *                      factura
 *   $nombreCliente, $servicio, $incluirOtros
 *   $urlExportarPdf -> enlace al PDF de esta misma comparativa
 *
 * =====================================================
 */

$e = fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

// Porcentaje corto para el texto: "21", "5,11", "0,5".
$porcentaje = fn($valor): string => rtrim(rtrim(number_format((float) $valor, 2, ',', ''), '0'), ',');

?>

<div class="comparar-resultado" id="resultadoComparativa">

    <div class="comparar-resultado-cabecera">

        <h2>Resultado de la comparativa</h2>

        <?php if ($avisoComparar === null && empty($comparativa['faltan_datos']) && !empty($comparativa['ofertas'])): ?>

            <!-- Informe en PDF con el diseño de NG Asesores (ver
                 exportar_comparativa_pdf.php), en una pestaña nueva. -->
            <a href="<?= $e($urlExportarPdf) ?>" target="_blank" rel="noopener" class="config-secondary-button">
                📄 Exportar PDF
            </a>

        <?php endif; ?>

    </div>

    <?php if ($avisoComparar !== null): ?>

        <div class="form-info">
            <p><?= $e($avisoComparar) ?></p>
        </div>

    <?php elseif (!empty($comparativa['faltan_datos'])): ?>

        <div class="form-error-general" style="display:block;">
            No se puede comparar: a la factura de <?= $e($nombreCliente) ?> le faltan
            <?= $e(implode(', ', $comparativa['faltan_datos'])) ?>.
            Complétalos en la tabla de arriba y guarda.
        </div>

    <?php else: ?>

        <?php
        $ofertas = $comparativa['ofertas'];
        $mejor = $ofertas[0] ?? null;
        $totalReferencia = $comparativa['total_referencia'];
        $dias = $comparativa['dias'];
        $actual = $comparativa['actual'];
        ?>

        <!-- Resumen: la mejor oferta -->

        <?php if ($mejor !== null && $mejor['ahorro'] > 0): ?>

            <div class="form-info registration-success comparar-resumen">
                <p>
                    La mejor opción para <strong><?= $e($nombreCliente) ?></strong> es
                    <strong><?= $e($mejor['comercializadora'] . ' — ' . $mejor['tarifa']) ?></strong>:
                    pagaría <strong><?= $e(formatearEuros($mejor['simulacion']['total'])) ?></strong>
                    en lugar de <?= $e(formatearEuros($totalReferencia)) ?>.
                    Ahorro de <strong><?= $e(formatearEuros($mejor['ahorro'])) ?></strong> en esta factura
                    (unos <strong><?= $e(formatearEuros(ahorroAnual($mejor['ahorro'], $dias))) ?></strong> al año).
                </p>
            </div>

        <?php elseif ($mejor !== null): ?>

            <div class="form-info comparar-resumen">
                <p>
                    Ninguna de las tarifas elegidas mejora la actual de
                    <strong><?= $e($nombreCliente) ?></strong> (<?= $e(formatearEuros($totalReferencia)) ?>).
                </p>
            </div>

        <?php endif; ?>

        <p class="comparar-ayuda">
            Factura de <?= (int) $dias ?> días.
            IVA <?= $e($porcentaje($tarifaCliente['iva'] ?? IVA_POR_DEFECTO)) ?> %<?php if ($servicio === 'luz'): ?>,
                impuesto eléctrico <?= $e($porcentaje($tarifaCliente['impuesto_electrico'] ?? IMPUESTO_ELECTRICO_POR_DEFECTO)) ?> %<?php endif; ?>.
            <?= $incluirOtros ? 'Las ofertas incluyen sus otros conceptos.' : 'Las ofertas no incluyen sus otros conceptos.' ?>
            El ahorro se calcula sobre
            <?= numeroComparativa($tarifaCliente['total_factura'] ?? null) !== null
                ? 'el total de su factura actual.'
                : 'su factura actual recalculada (no tiene total escrito).' ?>
        </p>

        <div class="comparar-tabla-contenedor">

            <table class="comparar-tabla">

                <thead>
                    <tr>
                        <th class="col-texto">Comercializadora</th>
                        <th class="col-texto">Tarifa</th>
                        <?php foreach ($comparativa['columnas'] as $columna): ?>
                            <th><?= $e($columna) ?></th>
                        <?php endforeach; ?>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Ahorro factura</th>
                        <th>Ahorro anual</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- Referencia: su tarifa actual -->
                    <tr class="comparar-fila-actual">
                        <td class="col-texto"><strong>Tarifa actual</strong></td>
                        <td class="col-texto"><?= $e($tarifaCliente['nombre'] !== '' ? $tarifaCliente['nombre'] : 'Sin nombre') ?></td>
                        <?php foreach ($comparativa['columnas'] as $columna): ?>
                            <td><?= $e(formatearEuros($actual['lineas'][$columna])) ?></td>
                        <?php endforeach; ?>
                        <td><?= $e(formatearEuros($actual['iva'])) ?></td>
                        <td>
                            <strong><?= $e(formatearEuros($totalReferencia)) ?></strong>
                            <?php if (abs($totalReferencia - $actual['total']) > 0.01): ?>
                                <small class="comparar-nota" title="Total recalculado con sus precios y consumos">
                                    recalculado: <?= $e(formatearEuros($actual['total'])) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>—</td>
                        <td>—</td>
                    </tr>

                    <?php foreach ($ofertas as $posicion => $oferta): ?>

                        <?php $esMejor = $posicion === 0 && $oferta['ahorro'] > 0; ?>

                        <tr<?= $esMejor ? ' class="comparar-fila-mejor"' : '' ?>>
                            <td class="col-texto">
                                <?= $e($oferta['comercializadora']) ?>
                                <?php if ($esMejor): ?>
                                    <span class="comparar-etiqueta comparar-mejor">Mejor opción</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-texto"><?= $e($oferta['tarifa']) ?></td>
                            <?php foreach ($comparativa['columnas'] as $columna): ?>
                                <td><?= $e(formatearEuros($oferta['simulacion']['lineas'][$columna])) ?></td>
                            <?php endforeach; ?>
                            <td><?= $e(formatearEuros($oferta['simulacion']['iva'])) ?></td>
                            <td><strong><?= $e(formatearEuros($oferta['simulacion']['total'])) ?></strong></td>
                            <td class="<?= $oferta['ahorro'] >= 0 ? 'ahorro-positivo' : 'ahorro-negativo' ?>">
                                <?= $e(formatearEuros($oferta['ahorro'])) ?>
                            </td>
                            <td class="<?= $oferta['ahorro'] >= 0 ? 'ahorro-positivo' : 'ahorro-negativo' ?>">
                                <?= $e(formatearEuros(ahorroAnual($oferta['ahorro'], $dias))) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <?php if (empty($ofertas) && empty($comparativa['incompletas'])): ?>

            <div class="form-info comparar-incompletas">
                <p>Las comercializadoras marcadas no tienen tarifas activas de este tipo.</p>
            </div>

        <?php endif; ?>

        <?php if (!empty($comparativa['incompletas'])): ?>

            <div class="form-info comparar-incompletas">
                <p><strong>No se han podido comparar</strong> (les faltan precios que este cliente necesita):</p>
                <ul>
                    <?php foreach ($comparativa['incompletas'] as $incompleta): ?>
                        <li>
                            <?= $e($incompleta['comercializadora'] . ' — ' . $incompleta['tarifa']) ?>:
                            falta <?= $e(implode(', ', $incompleta['simulacion']['faltan'])) ?>.
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php

/**
 * =====================================================
 * DATOS DE LA EMPRESA + DATOS DEL LOGIN (TARJETAS)
 * =====================================================
 *
 * Parte común de las tarjetas de confirmación de
 * guardar_empresa.php (creada), actualizar_empresa.php
 * (actualizada) y eliminar_empresa.php (eliminada), para
 * que las tres muestren lo mismo y en el mismo orden (ver
 * camposDatosEmpresa() en includes/empresas.php).
 *
 * Espera definidas:
 *   $empresa     -> fila de `empresas`
 *   $datosLogin  -> [[etiqueta, valor], ...] o null si no hay
 *                   usuario de acceso que mostrar
 *   $avisoLogin  -> texto del aviso bajo los datos del login,
 *                   o null para no mostrarlo
 *
 * =====================================================
 */

$e = fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

?>

<h2 class="confirmation-section-title">Datos de la empresa</h2>

<div class="usuario-detalle">

    <div class="usuario-detalle-grid">

        <?php foreach (camposDatosEmpresa($empresa) as $campo): ?>

            <div class="usuario-detalle-item <?= $e($campo['clase_item']) ?>">
                <span><?= $e($campo['etiqueta']) ?></span>
                <strong class="<?= $e($campo['clase_valor']) ?>"><?= $e($campo['valor']) ?></strong>
            </div>

        <?php endforeach; ?>

    </div>

</div>


<?php if (!empty($datosLogin)): ?>

    <div class="access-section">

        <h2 class="confirmation-section-title">Datos del login</h2>

        <div class="usuario-detalle">

            <div class="usuario-detalle-grid">

                <?php foreach ($datosLogin as [$etiqueta, $valor]): ?>

                    <div class="usuario-detalle-item">
                        <span><?= $e($etiqueta) ?></span>
                        <strong><?= $e($valor) ?></strong>
                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <?php if (!empty($avisoLogin)): ?>

            <div class="form-info">
                <p><?= $e($avisoLogin) ?></p>
            </div>

        <?php endif; ?>

    </div>

<?php endif; ?>

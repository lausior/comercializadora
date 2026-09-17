<?php

/**
 * Imprime un filtro de columna de selección múltiple: una barra
 * que es a la vez el resumen de lo marcado y un campo de texto
 * (se puede escribir directamente para buscar, o simplemente
 * pinchar para desplegar y elegir) y, al abrirla, un menú de
 * checkboxes (ver js/multi-select-filter.js para el
 * comportamiento de abrir/cerrar/marcar/buscar).
 *
 * Sustituye a los <select> de una sola opción en Clientes,
 * Usuarios, Empresas y Logs, donde ahora se puede marcar más de
 * un valor a la vez (y desmarcar) en el mismo filtro.
 *
 * @param string $id           Id único del filtro en la página (escritorio y móvil necesitan uno cada uno).
 * @param int    $columna      Índice de columna de la tabla (data-column), igual que en los filtros de texto.
 * @param string $etiquetaTodos Texto de la barra cuando no hay nada marcado (ej. "Todos", "Todas").
 * @param array  $opciones     Valores exactos a marcar; deben coincidir con el texto tal cual aparece en la celda.
 */
function filtroMultiSelect(
    string $id,
    int $columna,
    string $etiquetaTodos,
    array $opciones
): void {
    ?>

    <div
        class="column-filter multi-select-filter"
        data-column="<?= $columna ?>"
        data-placeholder="<?= htmlspecialchars($etiquetaTodos, ENT_QUOTES, 'UTF-8') ?>"
        id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
    >

        <div class="multi-select-toggle">

            <input
                type="text"
                class="multi-select-input"
                placeholder="<?= htmlspecialchars($etiquetaTodos, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="off"
                aria-expanded="false"
            >

            <span class="multi-select-toggle-icon">▾</span>

        </div>

        <div class="multi-select-menu" hidden>

            <?php if (empty($opciones)): ?>

                <p class="multi-select-empty">No hay opciones disponibles.</p>

            <?php else: ?>

                <div class="multi-select-options-list">

                    <?php foreach ($opciones as $opcion): ?>

                        <div class="multi-select-option">

                            <label class="multi-select-option-label">
                                <input type="checkbox" value="<?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>">
                                <span><?= htmlspecialchars($opcion) ?></span>
                            </label>

                            <button
                                type="button"
                                class="multi-select-option-remove"
                                aria-label="Quitar <?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                ✕
                            </button>

                        </div>

                    <?php endforeach; ?>

                    <p class="multi-select-no-results" hidden>Sin resultados.</p>

                    <button type="button" class="multi-select-clear-inline">
                        Deseleccionar todo
                    </button>

                </div>

            <?php endif; ?>

        </div>

    </div>

    <?php
}

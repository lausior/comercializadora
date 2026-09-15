<?php

/**
 * Imprime un filtro de columna de selección múltiple: un
 * botón que muestra cuántas opciones hay marcadas y, al
 * pulsarlo, un menú de checkboxes (ver js/multi-select-filter.js
 * para el comportamiento de abrir/cerrar/marcar).
 *
 * Sustituye a los <select> de una sola opción en Clientes,
 * Usuarios y Logs, donde ahora se puede marcar más de un
 * valor a la vez (y desmarcar) en el mismo filtro.
 *
 * @param string $id           Id único del filtro en la página (escritorio y móvil necesitan uno cada uno).
 * @param int    $columna      Índice de columna de la tabla (data-column), igual que en los filtros de texto.
 * @param string $etiquetaTodos Texto del botón cuando no hay nada marcado (ej. "Todos", "Todas").
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

        <button type="button" class="multi-select-toggle" aria-expanded="false">
            <span class="multi-select-toggle-label"><?= htmlspecialchars($etiquetaTodos) ?></span>
            <span class="multi-select-toggle-icon">▾</span>
        </button>

        <div class="multi-select-menu" hidden>

            <?php if (empty($opciones)): ?>

                <p class="multi-select-empty">No hay opciones disponibles.</p>

            <?php else: ?>

                <?php foreach ($opciones as $opcion): ?>

                    <label class="multi-select-option">
                        <input type="checkbox" value="<?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>">
                        <span><?= htmlspecialchars($opcion) ?></span>
                    </label>

                <?php endforeach; ?>

                <button type="button" class="multi-select-clear-inline">
                    Deseleccionar todo
                </button>

            <?php endif; ?>

        </div>

    </div>

    <?php
}

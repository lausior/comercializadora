<?php

/**
 * =====================================================
 * CAMPOS DEL FORMULARIO DE EMPRESA (CREAR / EDITAR)
 * =====================================================
 *
 * Se incluye desde crear_empresa.php y editar_empresa.php
 * (dentro de su <form>; los botones los pone cada página).
 * Espera definidas:
 *   $errorFormulario -> obtenerErrorFormulario()
 *   $datosPrevios    -> lo que se había escrito antes del error
 *   $empresa         -> datos iniciales: la fila de `empresas`
 *                       al editar, o solo ['codigo_empresa' => ...]
 *                       al crear
 *   $usuarioAcceso   -> usuario de acceso de la empresa
 *                       (obtenerUsuarioAccesoEmpresa()) o null
 *   $esEdicion       -> true en editar_empresa.php
 *
 * Diferencias entre crear y editar:
 *   - al crear, el email es obligatorio: se reutiliza como
 *     email del usuario de acceso;
 *   - al crear siempre se pide el username; al editar, solo
 *     si la empresa tiene usuario de acceso.
 *
 * =====================================================
 */

// Valor inicial de un campo: lo escrito antes de un error
// o, si no, el dato de la empresa. Ya escapado.
$valorEmpresa = fn(string $campo): string => valorFormulario(
    $datosPrevios,
    $campo,
    (string) ($empresa[$campo] ?? '')
);

$estadoPrevio = $datosPrevios['estado'] ?? ($empresa['estado'] ?? 'Activo');
$motivoPrevio = $datosPrevios['motivo_inactivo'] ?? ($empresa['motivo_inactivo'] ?? '');

$mostrarAcceso = !$esEdicion || $usuarioAcceso !== null;

// Campos de texto de "Datos de la empresa", en orden:
// [campo, etiqueta, tipo de input, obligatorio].
$camposTexto = [
    ['nombre', 'Nombre', 'text', true],
    ['cif', 'CIF', 'text', true],
    ['direccion', 'Dirección', 'text', false],
    ['telefono', 'Teléfono', 'tel', false],
    ['email', 'Email', 'email', !$esEdicion],
];

?>

<!-- =========================
     MENSAJE DE ERROR GENERAL
========================== -->

<div class="form-error-general" id="form-error-general" role="alert"
    style="display: <?= $errorFormulario ? 'block' : 'none' ?>;">
    <?= $errorFormulario ? htmlspecialchars($errorFormulario['mensaje'], ENT_QUOTES, 'UTF-8') : '' ?>
</div>


<div class="form-info">

    <p>
        El código de empresa se genera automáticamente
        y no se puede modificar.
    </p>

</div>


<div class="form-grid">


    <!-- =========================
         CÓDIGO DE EMPRESA
    ========================== -->

    <div class="form-group">

        <label for="codigo_empresa">
            Código de empresa
        </label>

        <input type="text" id="codigo_empresa" name="codigo_empresa" maxlength="6"
            class="<?= claseErrorCampo($errorFormulario, 'codigo_empresa') ?>"
            value="<?= htmlspecialchars((string) ($empresa['codigo_empresa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly>

        <span class="field-error"
            id="error-codigo_empresa"><?= mensajeErrorCampo($errorFormulario, 'codigo_empresa') ?></span>

    </div>


    <!-- =========================
         NOMBRE, CIF, DIRECCIÓN, TELÉFONO, EMAIL
    ========================== -->

    <?php foreach ($camposTexto as [$campo, $etiqueta, $tipo, $obligatorio]): ?>

        <div class="form-group">

            <label for="<?= $campo ?>">
                <?= $etiqueta ?>
            </label>

            <input type="<?= $tipo ?>" id="<?= $campo ?>" name="<?= $campo ?>"
                class="<?= claseErrorCampo($errorFormulario, $campo) ?>"
                value="<?= $valorEmpresa($campo) ?>" <?= $obligatorio ? 'required' : '' ?>>

            <span class="field-error"
                id="error-<?= $campo ?>"><?= mensajeErrorCampo($errorFormulario, $campo) ?></span>

        </div>

    <?php endforeach; ?>


    <!-- =========================
         ESTADO
    ========================== -->

    <div class="form-group">

        <label for="estado">
            Estado
        </label>

        <select id="estado" name="estado" class="<?= claseErrorCampo($errorFormulario, 'estado') ?>" required>
            <option value="Activo" <?= $estadoPrevio === 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $estadoPrevio === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>

        <span class="field-error"
            id="error-estado"><?= mensajeErrorCampo($errorFormulario, 'estado') ?></span>

    </div>


    <!-- =========================
         MOTIVO (SOLO SI INACTIVO)
         =========================
         Dentro de la rejilla, al lado de Estado. Lo muestra y
         oculta js/empresas.js según el Estado elegido.
    ========================== -->

    <div class="form-group <?= $estadoPrevio === 'Inactivo' ? '' : 'hidden' ?>" id="grupo_motivo_inactivo">

        <label for="motivo_inactivo">
            Motivo
        </label>

        <select id="motivo_inactivo" name="motivo_inactivo"
            class="<?= claseErrorCampo($errorFormulario, 'motivo_inactivo') ?>">
            <option value="">Selecciona un motivo</option>
            <?php pintarOpcionesMotivo(MOTIVOS_INACTIVO_EMPRESA, $motivoPrevio); ?>
        </select>

        <span class="field-error"
            id="error-motivo_inactivo"><?= mensajeErrorCampo($errorFormulario, 'motivo_inactivo') ?></span>

    </div>


</div>


<?php if ($mostrarAcceso): ?>

    <!-- =================================================
         ACCESO DE LA EMPRESA
         =================================================
         No es "un usuario que pertenece a la empresa": es el
         acceso de la propia empresa (rol EMPRESA), para no
         tener que crear la empresa y su login por separado en
         dos formularios. Por eso reutiliza el nombre, el email
         y el teléfono ya escritos arriba, y aquí solo hace falta
         el username — luego, desde ese acceso, la empresa podrá
         dar de alta a su equipo (rol Usuario). La contraseña no
         se edita aquí: al crear se asigna la inicial y al editar
         se restablece con el botón "Restablecer contraseña".
    ================================================== -->

    <h2>Acceso de la empresa</h2>

    <div class="form-info">

        <?php if ($esEdicion): ?>

            <p>
                La contraseña de acceso de la empresa
                no se modificará al guardar estos cambios.
            </p>

            <p>
                Para restablecerla a la contraseña inicial,
                usa el botón "Restablecer contraseña".
            </p>

        <?php else: ?>

            <p>
                La contraseña inicial se genera automáticamente y
                deberá cambiarla en su primer acceso.
            </p>

        <?php endif; ?>

    </div>

    <div class="form-grid">


        <!-- =========================
             USERNAME
        ========================== -->

        <div class="form-group">

            <label for="usuario_username">
                Username
            </label>

            <input type="text" id="usuario_username" name="usuario_username"
                class="<?= claseErrorCampo($errorFormulario, 'usuario_username') ?>"
                value="<?= valorFormulario($datosPrevios, 'usuario_username', (string) ($usuarioAcceso['username'] ?? '')) ?>" required>

            <span class="field-error"
                id="error-usuario_username"><?= mensajeErrorCampo($errorFormulario, 'usuario_username') ?></span>

        </div>


    </div>

<?php endif; ?>

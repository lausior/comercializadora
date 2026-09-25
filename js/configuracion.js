document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       AUTOGUARDAR AL CAMBIAR UN SELECT (bloqueo automático y
       retención de logs comparten el mismo comportamiento: se
       guardan en cuanto se cambia el valor, sin botón aparte).
    ========================================================= */

    ['formBloqueoAutomatico', 'formRetencionLogs'].forEach(idFormulario => {

        const formulario = document.getElementById(idFormulario);

        if (!formulario) {
            return;
        }

        const select = formulario.querySelector('select');

        if (!select) {
            return;
        }

        select.addEventListener('change', () => {
            formulario.submit();
        });

    });


    /* =========================================================
       MODAL: BORRAR LOGS AHORA
    ========================================================= */

    const modalBorrarLogs = document.getElementById('modalBorrarLogs');

    if (modalBorrarLogs) {

        window.abrirModalBorrarLogs = function () {
            modalBorrarLogs.style.display = 'flex';
        };

        window.cerrarModalBorrarLogs = function () {
            modalBorrarLogs.style.display = 'none';
        };

        modalBorrarLogs.addEventListener('click', event => {
            if (event.target === modalBorrarLogs) {
                window.cerrarModalBorrarLogs();
            }
        });

    }


    // Control de accesos: mismos rangos que
    // guardar_intentos_login.php (usa js/validacion-formulario.js).
    const entero = (minimo, maximo) => (valor, input) => {

        if (valor === '' && !(input.validity && input.validity.badInput)) {
            return 'Este campo es obligatorio.';
        }

        const numero = Number(valor);

        return /^\d+$/.test(valor) && numero >= minimo && numero <= maximo
            ? null
            : `Introduce un número entero entre ${minimo} y ${maximo}.`;

    };

    if (typeof inicializarValidacionFormulario === 'function') {

        inicializarValidacionFormulario(document.getElementById('formIntentosLogin'), {
            intentos_login_max: entero(1, 20),
            minutos_bloqueo_intentos: entero(1, 1440)
        });

    }

});

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

});

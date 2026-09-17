document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       TARJETAS DE DETALLE ARRASTRABLES
       Se puede mover cualquier .modal-detalle (Clientes,
       Usuarios, Empresas, Logs...) agarrando su cabecera. Al
       cerrarse el modal, la posición se resetea para que la
       próxima vez vuelva a salir centrada.
    ========================================================= */

    document.querySelectorAll('.modal-detalle').forEach(modal => {

        const cabecera = modal.querySelector('.modal-detalle-header');

        if (!cabecera) {
            return;
        }

        cabecera.classList.add('modal-detalle-arrastrable');

        let arrastrando = false;
        let offsetX = 0;
        let offsetY = 0;

        cabecera.addEventListener('mousedown', event => {

            // El botón de cerrar sigue funcionando con normalidad.
            if (event.target.closest('.modal-detalle-close')) {
                return;
            }

            const rect = modal.getBoundingClientRect();

            // Fija el ancho actual: al pasar a position:fixed,
            // el "width: 100%" de .modal-detalle pasaría a
            // calcularse sobre la ventana entera en vez de
            // sobre el overlay, y la tarjeta se vería enorme.
            modal.style.width = rect.width + 'px';
            modal.style.position = 'fixed';
            modal.style.margin = '0';
            modal.style.left = rect.left + 'px';
            modal.style.top = rect.top + 'px';

            offsetX = event.clientX - rect.left;
            offsetY = event.clientY - rect.top;

            arrastrando = true;

            document.body.classList.add('modal-detalle-arrastrando');

            event.preventDefault();

        });

        document.addEventListener('mousemove', event => {

            if (!arrastrando) {
                return;
            }

            let nuevoLeft = event.clientX - offsetX;
            let nuevoTop = event.clientY - offsetY;

            nuevoLeft = Math.max(0, Math.min(nuevoLeft, window.innerWidth - modal.offsetWidth));
            nuevoTop = Math.max(0, Math.min(nuevoTop, window.innerHeight - modal.offsetHeight));

            modal.style.left = nuevoLeft + 'px';
            modal.style.top = nuevoTop + 'px';

        });

        document.addEventListener('mouseup', () => {

            if (arrastrando) {
                arrastrando = false;
                document.body.classList.remove('modal-detalle-arrastrando');
            }

        });

    });


    /* =========================================================
       RESETEAR POSICIÓN AL CERRAR
       Cada página cierra su modal poniendo style.display a
       "none"; en cuanto lo detectamos, quitamos la posición
       fija para que la próxima apertura salga centrada de
       nuevo (comportamiento por defecto del .modal-overlay).
    ========================================================= */

    document.querySelectorAll('.modal-overlay').forEach(overlay => {

        const modal = overlay.querySelector('.modal-detalle');

        if (!modal) {
            return;
        }

        const observador = new MutationObserver(() => {

            if (overlay.style.display === 'none') {
                modal.style.position = '';
                modal.style.left = '';
                modal.style.top = '';
                modal.style.margin = '';
                modal.style.width = '';
            }

        });

        observador.observe(overlay, {
            attributes: true,
            attributeFilter: ['style']
        });

    });

});

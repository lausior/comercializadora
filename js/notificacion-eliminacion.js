window.mostrarNotificacionEliminacion = function (datos) {

    const existente = document.getElementById('notificacionEliminacion');

    if (existente) {
        existente.remove();
    }

    const overlay = document.createElement('div');
    overlay.id = 'notificacionEliminacion';
    overlay.className = 'modal-overlay modal-overlay-notificacion';

    const tarjeta = document.createElement('div');
    tarjeta.className = 'modal-confirmacion modal-eliminacion-exito';
    tarjeta.setAttribute('role', 'alertdialog');
    tarjeta.setAttribute('aria-modal', 'true');

    const icono = document.createElement('div');
    icono.className = 'modal-icon modal-icon-exito';
    icono.textContent = '✓';

    const titulo = document.createElement('h2');
    titulo.textContent = `${datos.tipo} eliminado correctamente`;

    tarjeta.append(icono, titulo);

    // =========================================================
    // MENSAJE DE CONFIRMACIÓN (TABLETA VERDE)
    // =========================================================
    // Mismo estilo que usan las tarjetas de crear/actualizar
    // (.form-info.registration-success), reutilizado aquí.

    const mensaje = document.createElement('div');
    mensaje.className = 'form-info registration-success eliminacion-mensaje';

    const mensajeTexto = document.createElement('p');
    mensajeTexto.textContent = `Se ha eliminado ${datos.tipo.toLowerCase()} ${datos.nombre}.`;
    mensaje.append(mensajeTexto);

    tarjeta.append(mensaje);

    // =========================================================
    // SECCIONES (Datos de la empresa / Datos del login...)
    // =========================================================
    // Formato nuevo, con separación + título + grid de 2 columnas
    // por sección. Si el backend todavía envía el formato plano
    // antiguo (datos.campos), se mantiene como alternativa.

    if (Array.isArray(datos.secciones) && datos.secciones.length > 0) {

        const contenedorSecciones = document.createElement('div');
        contenedorSecciones.className = 'eliminacion-secciones';

        datos.secciones.forEach(seccion => {

            const tituloSeccion = document.createElement('p');
            tituloSeccion.className = 'eliminacion-seccion-titulo';
            tituloSeccion.textContent = seccion.titulo;

            const detalle = document.createElement('div');
            detalle.className = 'usuario-detalle';

            const grid = document.createElement('div');
            grid.className = 'usuario-detalle-grid';

            (seccion.campos || []).forEach(([etiqueta, valor]) => {

                const item = document.createElement('div');
                item.className = 'usuario-detalle-item';

                if (etiqueta !== null) {

                    const etiquetaElemento = document.createElement('span');
                    const valorElemento = document.createElement('strong');

                    etiquetaElemento.textContent = etiqueta;
                    valorElemento.textContent = valor || 'No indicado';

                    if (etiqueta === 'Estado') {
                        valorElemento.classList.add(
                            valor === 'Activo' ? 'text-success' : 'text-danger'
                        );
                    }

                    item.append(etiquetaElemento, valorElemento);

                }

                grid.append(item);

            });

            detalle.append(grid);
            contenedorSecciones.append(tituloSeccion, detalle);

        });

        tarjeta.append(contenedorSecciones);

    } else if (datos.campos) {

        const lista = document.createElement('div');
        lista.className = 'eliminacion-datos';

        Object.entries(datos.campos).forEach(([etiqueta, valor]) => {
            const fila = document.createElement('div');
            const etiquetaElemento = document.createElement('span');
            const valorElemento = document.createElement('strong');

            etiquetaElemento.textContent = etiqueta;
            valorElemento.textContent = valor || 'No indicado';
            fila.append(etiquetaElemento, valorElemento);
            lista.append(fila);
        });

        tarjeta.append(lista);

    }

    const acciones = document.createElement('div');
    acciones.className = 'modal-actions';

    const cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'modal-button modal-button-primary';
    cerrar.textContent = 'Continuar';
    cerrar.addEventListener('click', cerrarNotificacion);

    acciones.append(cerrar);
    tarjeta.append(acciones);
    overlay.append(tarjeta);
    document.body.append(overlay);
    document.body.classList.add('modal-abierto');
    cerrar.focus();

    overlay.addEventListener('click', event => {
        if (event.target === overlay) {
            cerrarNotificacion();
        }
    });

    function cerrarNotificacion() {
        overlay.remove();
        document.body.classList.remove('modal-abierto');
    }

    overlay.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            cerrarNotificacion();
        }
    });
};

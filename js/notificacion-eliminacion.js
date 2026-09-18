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

    const texto = document.createElement('p');
    texto.textContent = `Se ha eliminado ${datos.tipo.toLowerCase()} ${datos.nombre}.`;

    const lista = document.createElement('div');
    lista.className = 'eliminacion-datos';

    Object.entries(datos.campos || {}).forEach(([etiqueta, valor]) => {
        const fila = document.createElement('div');
        const etiquetaElemento = document.createElement('span');
        const valorElemento = document.createElement('strong');

        etiquetaElemento.textContent = etiqueta;
        valorElemento.textContent = valor || 'No indicado';
        fila.append(etiquetaElemento, valorElemento);
        lista.append(fila);
    });

    const acciones = document.createElement('div');
    acciones.className = 'modal-actions';

    const cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'modal-button modal-button-primary';
    cerrar.textContent = 'Continuar';
    cerrar.addEventListener('click', cerrarNotificacion);

    acciones.append(cerrar);
    tarjeta.append(icono, titulo, texto, lista, acciones);
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

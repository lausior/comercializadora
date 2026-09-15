document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       01. MODAL DE ELIMINACIÓN
       Mismo patrón que en clientes.js/usuarios.js/empresas.js.
    ========================================================= */

    let tareaEliminarId = 0;

    const modalEliminar = document.getElementById('modalEliminarTarea');
    const nombreTareaEliminar = document.getElementById('nombreTareaEliminar');

    window.abrirModalEliminarTarea = function (id, titulo) {

        tareaEliminarId = Number(id);

        if (nombreTareaEliminar) {
            nombreTareaEliminar.textContent = titulo;
        }

        if (modalEliminar) {
            modalEliminar.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };

    window.cerrarModalEliminarTarea = function () {

        tareaEliminarId = 0;

        if (modalEliminar) {
            modalEliminar.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    window.confirmarEliminarTarea = function () {

        if (tareaEliminarId <= 0) {
            return;
        }

        window.location.href = 'eliminar_tarea.php?id=' + encodeURIComponent(tareaEliminarId);

    };

    if (modalEliminar) {

        modalEliminar.addEventListener('click', (event) => {

            if (event.target === modalEliminar) {
                window.cerrarModalEliminarTarea();
            }

        });

    }

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape' && tareaEliminarId > 0) {
            window.cerrarModalEliminarTarea();
        }

    });


    /* =========================================================
       02. FILTROS (Estado + Responsable)
       =========================================================
       Se aplican tanto a las tarjetas de tarea del calendario
       como a las filas de "Tareas recientes". No hay tabla ni
       paginación aquí, así que solo se oculta/muestra cada
       elemento según si coincide con lo filtrado.
    ========================================================= */

    const filtroEstado = document.getElementById('filtroEstadoTarea');
    const filtroResponsable = document.getElementById('filtroResponsableTarea');

    const tareasCalendario = document.querySelectorAll('.calendar-task');
    const filasRecientes = document.querySelectorAll('.planner-table-row[data-estado]');

    function normalizar(texto) {

        return String(texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .trim();

    }

    function aplicarFiltrosPlanificador() {

        const estadosSeleccionados = filtroEstado
            ? window.obtenerSeleccionMultiFiltro(filtroEstado).map(normalizar)
            : [];

        const responsableBuscado = filtroResponsable
            ? normalizar(filtroResponsable.value)
            : '';

        function coincide(elemento) {

            const estado = normalizar(elemento.dataset.estado || '');
            const responsable = normalizar(elemento.dataset.responsable || '');

            const coincideEstado =
                estadosSeleccionados.length === 0 ||
                estadosSeleccionados.includes(estado);

            const coincideResponsable =
                responsableBuscado === '' ||
                responsable.includes(responsableBuscado);

            return coincideEstado && coincideResponsable;

        }

        [...tareasCalendario, ...filasRecientes].forEach(elemento => {
            elemento.style.display = coincide(elemento) ? '' : 'none';
        });

    }

    if (filtroEstado) {
        filtroEstado.addEventListener('change', aplicarFiltrosPlanificador);
    }

    if (filtroResponsable) {
        filtroResponsable.addEventListener('input', aplicarFiltrosPlanificador);
    }

});

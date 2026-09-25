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

        const responsablesSeleccionados = filtroResponsable
            ? window.obtenerSeleccionMultiFiltro(filtroResponsable).map(normalizar)
            : [];

        // Mismo criterio que los filtros de Usuarios: dentro de
        // un filtro vale CUALQUIERA de las opciones marcadas;
        // entre filtros, deben cumplirse todos.
        function coincide(elemento) {

            const estado = normalizar(elemento.dataset.estado || '');
            const responsable = normalizar(elemento.dataset.responsable || '') || 'sin responsable';

            const coincideEstado =
                estadosSeleccionados.length === 0 ||
                estadosSeleccionados.includes(estado);

            const coincideResponsable =
                responsablesSeleccionados.length === 0 ||
                responsablesSeleccionados.includes(responsable);

            return coincideEstado && coincideResponsable;

        }

        [...tareasCalendario, ...filasRecientes].forEach(elemento => {
            elemento.style.display = coincide(elemento) ? '' : 'none';
        });

    }

    const filtrosPlanificador = [filtroEstado, filtroResponsable].filter(Boolean);

    if (filtrosPlanificador.length > 0 && typeof window.crearMemoriaFiltros === 'function') {

        // Recordar los filtros al cambiar de mes o volver de
        // crear/editar una tarea (la página se recarga).
        const memoriaFiltros = window.crearMemoriaFiltros('filtrosPlanificador', filtrosPlanificador);

        filtrosPlanificador.forEach(filtro => {
            filtro.addEventListener('change', () => {
                memoriaFiltros.guardar();
                aplicarFiltrosPlanificador();
            });
        });

        const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');

        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', () => {
                filtrosPlanificador.forEach(filtro => window.limpiarMultiFiltro(filtro));
                memoriaFiltros.olvidar();
                aplicarFiltrosPlanificador();
            });
        }

        memoriaFiltros.restaurar();
        aplicarFiltrosPlanificador();

    }


    /* =========================================================
       03. FORMULARIO CREAR / EDITAR TAREA
       Mismas reglas que validarDatosTarea() (includes/
       tareas.php). Usa js/validacion-formulario.js.
    ========================================================= */

    const formularioTarea = document.querySelector(
        'form[action="guardar_tarea.php"], form[action="actualizar_tarea.php"]'
    );

    if (formularioTarea && typeof inicializarValidacionFormulario === 'function') {

        const obligatorio = mensaje => valor => valor === '' ? mensaje : null;

        inicializarValidacionFormulario(formularioTarea, {

            titulo: valor => {
                if (valor === '') return 'Este campo es obligatorio.';
                if (valor.length > 150) return 'No puede superar los 150 caracteres.';
                return null;
            },

            area: obligatorio('Selecciona un área.'),

            fecha: (valor, input) => {
                if (valor === '') return 'Selecciona una fecha.';
                // Un <input type="date"> con fecha a medio escribir
                // da value vacío pero badInput.
                if (input.validity && input.validity.badInput) return 'Selecciona una fecha válida.';
                return /^\d{4}-\d{2}-\d{2}$/.test(valor) ? null : 'Selecciona una fecha válida.';
            },

            hora: (valor, input) => {
                if (input.validity && input.validity.badInput) return 'Introduce una hora válida.';
                return valor === '' || /^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(valor)
                    ? null
                    : 'Introduce una hora válida.';
            },

            responsable: valor => valor.length > 100 ? 'No puede superar los 100 caracteres.' : null,

            estado: obligatorio('Selecciona un estado.')

        });

    }

});

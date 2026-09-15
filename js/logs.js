document.addEventListener('DOMContentLoaded', () => {

    const table = document.querySelector('.logs-table');

    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    let rows = Array.from(tbody.querySelectorAll('tr'));

    const mostrando = document.querySelector('.logs-footer > span');

    const botonesPaginacion = document.querySelectorAll(
        '.logs-footer .pagination-button'
    );

    const selectorPorPagina = document.getElementById('logsPorPagina');

    let logsPorPagina = 5;

    let paginaActual = 1;

    let filasFiltradasActuales = [];

    const filtroTipo = document.getElementById('tipo');
    const filtroUsuario = document.getElementById('usuario');
    const filtroFecha = document.getElementById('fecha');
    const filtroEvento = document.getElementById('evento');
    const filtroDescripcion = document.getElementById('descripcion');
    const filtroIp = document.getElementById('ip');

    const botonFiltrar = document.querySelector('.logs-filters .logs-btn.primary');

    const sortButtons = table.querySelectorAll('.sort-button');

    /*
    ========================================
    FILTRADO
    ========================================
    */

    function aplicarFiltros() {

        // Tipo y Usuario admiten marcar varias opciones a la
        // vez (ver js/multi-select-filter.js); si no hay
        // ninguna marcada, el filtro no se aplica.
        const tiposSeleccionados = filtroTipo
            ? window.obtenerSeleccionMultiFiltro(filtroTipo).map(v => v.toLowerCase().trim())
            : [];

        const usuariosSeleccionados = filtroUsuario
            ? window.obtenerSeleccionMultiFiltro(filtroUsuario).map(v => v.toLowerCase().trim())
            : [];

        const fechaSeleccionada = filtroFecha
            ? filtroFecha.value
            : '';

        const eventoSeleccionado = filtroEvento
            ? filtroEvento.value.toLowerCase().trim()
            : '';

        const descripcionSeleccionada = filtroDescripcion
            ? filtroDescripcion.value.toLowerCase().trim()
            : '';

        const ipSeleccionada = filtroIp
            ? filtroIp.value.toLowerCase().trim()
            : '';

        filasFiltradasActuales = rows.filter(row => {

            const celdas = row.querySelectorAll('td');

            if (celdas.length < 6) {
            return false;
            }

            const tipo = celdas[1]
                .textContent
                .toLowerCase()
                .trim();

            const usuario = celdas[2]
                .textContent
                .toLowerCase()
                .trim();

            const fechaHora = celdas[0]
                .textContent
                .trim();

            const evento = celdas[3]
                .textContent
                .toLowerCase()
                .trim();

            const descripcion = celdas[4]
                .textContent
                .toLowerCase()
                .trim();

            const ip = celdas[5]
                .textContent
                .toLowerCase()
                .trim();

            /*
            Convertimos:
            03/09/2026 10:42:15
            en:
            2026-09-03
            */

            let fechaFila = '';

            const coincidencia = fechaHora.match(
                /^(\d{2})\/(\d{2})\/(\d{4})/
            );

            if (coincidencia) {

                const dia = coincidencia[1];
                const mes = coincidencia[2];
                const anio = coincidencia[3];

                fechaFila = `${anio}-${mes}-${dia}`;
            }


            const coincideTipo =
                tiposSeleccionados.length === 0 ||
                tiposSeleccionados.includes(tipo);


            const coincideUsuario =
                usuariosSeleccionados.length === 0 ||
                usuariosSeleccionados.includes(usuario);


            const coincideFecha =
                fechaSeleccionada === '' ||
                fechaFila === fechaSeleccionada;

            const coincideEvento =
                eventoSeleccionado === '' ||
                evento.includes(eventoSeleccionado);

            const coincideDescripcion =
                descripcionSeleccionada === '' ||
                descripcion.includes(descripcionSeleccionada);

            const coincideIp =
                ipSeleccionada === '' ||
                ip.includes(ipSeleccionada);


            return (
                coincideTipo &&
                coincideUsuario &&
                coincideFecha &&
                coincideEvento &&
                coincideDescripcion &&
                coincideIp
            );

        });

        const totalPaginas = Math.max(
            1,
            logsPorPagina === Infinity
                ? 1
                : Math.ceil(
                    filasFiltradasActuales.length /
                    logsPorPagina
                )
        );

        paginaActual = Math.min(
            paginaActual,
            totalPaginas
        );

        const inicio = logsPorPagina === Infinity
            ? 0
            : (paginaActual - 1) * logsPorPagina;

        const filasPagina = filasFiltradasActuales.slice(
            inicio,
            logsPorPagina === Infinity
                ? filasFiltradasActuales.length
                : inicio + logsPorPagina
        );

        rows.forEach(row => {
            row.style.display = filasPagina.includes(row)
                ? ''
                : 'none';
        });

        actualizarContador(
            filasFiltradasActuales.length,
            inicio,
            filasPagina.length
        );

        actualizarPaginacion(totalPaginas);


    }


    /*
    ========================================
    CONTADOR
    ========================================
    */

    function actualizarContador(cantidad, inicio, cantidadPagina) {

        if (!mostrando) {
            return;
        }

        if (cantidad === 0) {
            mostrando.textContent = 'Mostrando 0 de 0 registros';
            return;
        }

        mostrando.textContent =
            `Mostrando ${inicio + 1}-${inicio + cantidadPagina} de ${cantidad} registros`;

    }


    if (selectorPorPagina) {

        selectorPorPagina.addEventListener('change', () => {

            logsPorPagina = selectorPorPagina.value === 'all'
                ? Infinity
                : Number(selectorPorPagina.value);

            paginaActual = 1;

            aplicarFiltros();

        });

    }


    function actualizarPaginacion(totalPaginas) {

        botonesPaginacion.forEach(boton => {

            const accion = boton.dataset.page;

            if (accion === 'prev') {
                boton.disabled = paginaActual <= 1;
                boton.classList.toggle(
                    'disabled',
                    paginaActual <= 1
                );
                return;
            }

            if (accion === 'next') {
                boton.disabled = paginaActual >= totalPaginas;
                boton.classList.toggle(
                    'disabled',
                    paginaActual >= totalPaginas
                );
                return;
            }

            const numeroPagina = Number(accion);

            if (!Number.isNaN(numeroPagina)) {
                boton.style.display = numeroPagina > totalPaginas
                    || logsPorPagina === Infinity
                        ? 'none'
                        : 'inline-flex';

                boton.classList.toggle(
                    'active',
                    numeroPagina === paginaActual
                );
            }

        });

    }


    /*
    ========================================
    BOTÓN FILTRAR
    ========================================
    */

    if (botonFiltrar) {

        botonFiltrar.addEventListener('click', (event) => {

            event.preventDefault();

            paginaActual = 1;

            aplicarFiltros();

        });

    }


    /*
    ========================================
    FILTRADO AUTOMÁTICO
    ========================================
    */

    if (filtroTipo) {

        filtroTipo.addEventListener('change', () => {
            paginaActual = 1;
            aplicarFiltros();
        });

    }


    if (filtroUsuario) {

        filtroUsuario.addEventListener('change', () => {
            paginaActual = 1;
            aplicarFiltros();
        });

    }


    if (filtroFecha) {

        filtroFecha.addEventListener('change', () => {
            paginaActual = 1;
            aplicarFiltros();
        });

    }


    [
        filtroEvento,
        filtroDescripcion,
        filtroIp
    ].forEach(filtro => {

        if (!filtro) {
            return;
        }

        filtro.addEventListener('input', () => {
            paginaActual = 1;
            aplicarFiltros();
        });

    });


    botonesPaginacion.forEach(boton => {

        boton.addEventListener('click', () => {

            const accion = boton.dataset.page;

            if (accion === 'prev' && paginaActual > 1) {
                paginaActual--;
            }

            if (accion === 'next') {
                const totalPaginas = Math.max(
                    1,
                    Math.ceil(
                        filasFiltradasActuales.length /
                        logsPorPagina
                    )
                );

                if (paginaActual < totalPaginas) {
                    paginaActual++;
                }
            }

            const numeroPagina = Number(accion);

            if (!Number.isNaN(numeroPagina)) {
                paginaActual = numeroPagina;
            }

            aplicarFiltros();

        });

    });


    /*
    ========================================
    ORDENACIÓN
    ========================================
    */

    sortButtons.forEach((button, index) => {

        button.dataset.order = 'none';


        button.addEventListener('click', () => {

            let orden;

            if (button.dataset.order === 'none' ||
                button.dataset.order === 'desc') {

                orden = 'asc';

            } else {

                orden = 'desc';

            }


            sortButtons.forEach(otherButton => {

                if (otherButton !== button) {

                    otherButton.dataset.order = 'none';
                    otherButton.textContent = '↕';

                }

            });


            button.dataset.order = orden;


            if (orden === 'asc') {

                button.textContent = '↑';

            } else {

                button.textContent = '↓';

            }


            ordenarTabla(index, orden);

        });

    });


    /*
    ========================================
    ORDENAR TABLA
    ========================================
    */

    function ordenarTabla(columna, orden) {

        const filas = Array.from(
            tbody.querySelectorAll('tr')
        );


        filas.sort((filaA, filaB) => {

            const valorA = obtenerValor(
                filaA,
                columna
            );

            const valorB = obtenerValor(
                filaB,
                columna
            );


            let comparacion = 0;


            /*
            FECHA
            */

            if (columna === 0) {

                const fechaA = convertirFecha(valorA);
                const fechaB = convertirFecha(valorB);

                comparacion = fechaA - fechaB;

            }


            /*
            TEXTO
            */

            else {

                comparacion = valorA.localeCompare(
                    valorB,
                    'es',
                    {
                        sensitivity: 'base'
                    }
                );

            }


            return orden === 'asc'
                ? comparacion
                : -comparacion;

        });


        filas.forEach(fila => {
            tbody.appendChild(fila);
        });

        rows = filas;
        paginaActual = 1;

        aplicarFiltros();

    }


    /*
    ========================================
    OBTENER VALOR DE COLUMNA
    ========================================
    */

    function obtenerValor(fila, columna) {

        const celda = fila.querySelectorAll('td')[columna];

        if (!celda) {
            return '';
        }

        return celda.textContent
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

    }


    /*
    ========================================
    CONVERTIR FECHA
    ========================================
    */

    function convertirFecha(fechaTexto) {

        const coincidencia = fechaTexto.match(
            /^(\d{2})\/(\d{2})\/(\d{4})\s*(\d{2})?:?(\d{2})?:?(\d{2})?/
        );


        if (!coincidencia) {
            return 0;
        }


        const dia = parseInt(coincidencia[1], 10);
        const mes = parseInt(coincidencia[2], 10) - 1;
        const anio = parseInt(coincidencia[3], 10);

        const horas = parseInt(
            coincidencia[4] || '0',
            10
        );

        const minutos = parseInt(
            coincidencia[5] || '0',
            10
        );

        const segundos = parseInt(
            coincidencia[6] || '0',
            10
        );


        return new Date(
            anio,
            mes,
            dia,
            horas,
            minutos,
            segundos
        ).getTime();

    }


    /*
    ========================================
    INICIALIZAR
    ========================================
    */

    aplicarFiltros();

});
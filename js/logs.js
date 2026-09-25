document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       01. ELEMENTOS DEL DOM (LISTADO)
    ========================================================= */

    const tbody = document.querySelector('.logs-table tbody');
    const tabla = document.querySelector('.logs-table');

    const contador = document.getElementById('logsContador');
    const mostrando = document.getElementById('logsMostrando');

    const selectorPorPagina = document.getElementById('logsPorPagina');

    // Los filtros por columna de escritorio y los del panel
    // móvil usan la misma clase y el mismo data-column, así
    // que un único selector los recoge a todos: solo el que
    // esté visible en cada momento tendrá valor.
    const filtros = document.querySelectorAll('.column-filter');

    const botonesOrden = document.querySelectorAll('.logs-table .sort-button');

    const botonesPaginacion = document.querySelectorAll('.logs-footer .pagination-button');

    if (!tbody || !tabla) {
        return;
    }


    /* =========================================================
       02. CONFIGURACIÓN
    ========================================================= */

    let filas = Array.from(tbody.querySelectorAll('tr'));

    let LOGS_POR_PAGINA = 5;

    let paginaActual = 1;

    let LOGS_TOTALES = filas.length;


    /* =========================================================
       03. ORDEN ORIGINAL
    ========================================================= */

    filas.forEach((fila, index) => {
        fila.dataset.originalOrder = index;
    });


    /* =========================================================
       04. NORMALIZAR TEXTO
    ========================================================= */

    function normalizar(texto) {

        return String(texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .trim();

    }


    /* =========================================================
       05. OBTENER TEXTO DE UNA CELDA
       =========================================================
       La columna 0 (Usuario) es la única siempre visible: su
       celda es la tarjeta avatar+usuario+evento, así que el
       "texto" a filtrar/ordenar se saca del <strong> (el
       usuario), no de la celda entera.
    ========================================================= */

    function obtenerTextoCelda(fila, columna) {

        const celdas = fila.querySelectorAll('td');

        if (!celdas[columna]) {
            return '';
        }

        if (columna === 0) {

            const nombre = celdas[0].querySelector('strong');
            return normalizar(nombre ? nombre.textContent : celdas[0].textContent);

        }

        return normalizar(celdas[columna].textContent);

    }


    /* =========================================================
       05B. VALOR "LIMPIO" DE UNA COLUMNA PARA LOS FILTROS
       DESPLEGABLES
       =========================================================
       Usamos los data-* de la fila (ya traen el valor limpio
       de cada campo) en vez del texto de la celda, para que
       las opciones marcadas en el desplegable coincidan de
       forma exacta.
    ========================================================= */

    const CAMPO_POR_COLUMNA = {
        0: 'usuario',
        1: 'tipo',
        2: 'evento',
        3: 'descripcion',
        4: 'fechaFiltro',
        5: 'ip'
    };

    function obtenerValorFiltroFila(fila, columna) {

        // La columna 4 (Fecha) se filtra por día completo
        // ("dd/mm/aaaa"), no por la fecha+hora que se muestra
        // en la celda; ver data-fecha-filtro más abajo.
        if (columna === 4) {
            return normalizar(fila.dataset.fechaFiltro || '');
        }

        const campo = CAMPO_POR_COLUMNA[columna];

        if (campo && fila.dataset[campo] !== undefined) {
            return normalizar(fila.dataset[campo]);
        }

        return obtenerTextoCelda(fila, columna);

    }


    /* =========================================================
       06. FILTRADO
    ========================================================= */

    function obtenerFilasFiltradas() {

        return filas.filter(fila => {

            let coincide = true;

            filtros.forEach(filtro => {

                const columna = Number(filtro.dataset.column);

                const seleccionados = window.obtenerSeleccionMultiFiltro(filtro)
                    .map(normalizar);

                if (seleccionados.length === 0) {
                    return;
                }

                const valorFila = obtenerValorFiltroFila(fila, columna);

                if (!seleccionados.includes(valorFila)) {
                    coincide = false;
                }

            });

            return coincide;

        });

    }


    /* =========================================================
       07. TOTAL DE PÁGINAS
    ========================================================= */

    function obtenerTotalPaginas(filasFiltradas) {

        if (filasFiltradas.length === 0) {
            return 1;
        }

        if (LOGS_POR_PAGINA === Infinity) {
            return 1;
        }

        return Math.ceil(filasFiltradas.length / LOGS_POR_PAGINA);

    }


    /* =========================================================
       08. MOSTRAR PÁGINA
    ========================================================= */

    function mostrarPagina() {

        const filasFiltradas = obtenerFilasFiltradas();
        const totalPaginas = obtenerTotalPaginas(filasFiltradas);

        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        if (paginaActual < 1) {
            paginaActual = 1;
        }

        filas.forEach(fila => {
            fila.style.display = 'none';
        });

        const inicio = LOGS_POR_PAGINA === Infinity
            ? 0
            : (paginaActual - 1) * LOGS_POR_PAGINA;

        const fin = LOGS_POR_PAGINA === Infinity
            ? filasFiltradas.length
            : inicio + LOGS_POR_PAGINA;

        const filasPagina = filasFiltradas.slice(inicio, fin);

        filasPagina.forEach(fila => {
            fila.style.display = '';
        });

        actualizarContadores(filasFiltradas);
        actualizarPaginacion(totalPaginas);

    }


    /* =========================================================
       09. CONTADORES
    ========================================================= */

    function actualizarContadores(filasFiltradas) {

        const cantidadFiltrada = filasFiltradas.length;

        if (contador) {

            contador.textContent = cantidadFiltrada === 1
                ? '1 registro encontrado'
                : `${cantidadFiltrada} registros encontrados`;

        }

        if (mostrando) {

            if (cantidadFiltrada === 0) {

                mostrando.textContent = `Mostrando 0 de ${LOGS_TOTALES} registros`;

                return;

            }

            if (LOGS_POR_PAGINA === Infinity) {

                mostrando.textContent = `Mostrando ${cantidadFiltrada} de ${cantidadFiltrada} registros`;

                return;

            }

            const inicio = (paginaActual - 1) * LOGS_POR_PAGINA + 1;

            const fin = Math.min(
                inicio + LOGS_POR_PAGINA - 1,
                cantidadFiltrada
            );

            mostrando.textContent = `Mostrando ${inicio}-${fin} de ${cantidadFiltrada} registros`;

        }

    }


    /* =========================================================
       10. PAGINACIÓN
    ========================================================= */

    function actualizarPaginacion(totalPaginas) {

        botonesPaginacion.forEach(boton => {

            const accion = boton.dataset.page;

            if (accion === 'prev') {

                const deshabilitado = paginaActual <= 1;

                boton.disabled = deshabilitado;
                boton.classList.toggle('disabled', deshabilitado);

                return;

            }

            if (accion === 'next') {

                const deshabilitado = paginaActual >= totalPaginas;

                boton.disabled = deshabilitado;
                boton.classList.toggle('disabled', deshabilitado);

                return;

            }

            const numeroPagina = Number(accion);

            if (!Number.isNaN(numeroPagina)) {

                boton.style.display =
                    (LOGS_POR_PAGINA === Infinity || numeroPagina > totalPaginas)
                        ? 'none'
                        : 'inline-flex';

                boton.classList.toggle('active', numeroPagina === paginaActual);

            }

        });

    }

    botonesPaginacion.forEach(boton => {

        boton.addEventListener('click', () => {

            const accion = boton.dataset.page;

            if (accion === 'prev') {

                if (paginaActual > 1) {
                    paginaActual--;
                    mostrarPagina();
                }

                return;

            }

            if (accion === 'next') {

                const filasFiltradas = obtenerFilasFiltradas();
                const totalPaginas = obtenerTotalPaginas(filasFiltradas);

                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    mostrarPagina();
                }

                return;

            }

            const numeroPagina = Number(accion);

            if (!Number.isNaN(numeroPagina)) {
                paginaActual = numeroPagina;
                mostrarPagina();
            }

        });

    });


    /* =========================================================
       11. FILTROS
    ========================================================= */

    // Recordar los filtros al recargar la página (mismo
    // comportamiento que Usuarios; ver crearMemoriaFiltros() en
    // js/multi-select-filter.js).
    const memoriaFiltros = window.crearMemoriaFiltros('filtrosLogs', filtros);

    filtros.forEach(filtro => {

        filtro.addEventListener('input', () => {
            memoriaFiltros.guardar();
            paginaActual = 1;
            mostrarPagina();
        });

        filtro.addEventListener('change', () => {
            memoriaFiltros.guardar();
            paginaActual = 1;
            mostrarPagina();
        });

    });


    /* =========================================================
       12. LIMPIAR FILTROS
       Hay dos botones "Limpiar filtros" (el de la tabla de
       escritorio y el del panel móvil); los dos vacían el
       mismo conjunto de inputs, escritorio y móvil incluidos.
    ========================================================= */

    document.querySelectorAll('#btnLimpiarFiltros, #btnLimpiarFiltrosMovil').forEach(btn => {

        btn.addEventListener('click', () => {

            filtros.forEach(filtro => {
                window.limpiarMultiFiltro(filtro);
            });

            memoriaFiltros.olvidar();

            paginaActual = 1;
            mostrarPagina();

        });

    });


    /* =========================================================
       13. REGISTROS POR PÁGINA
    ========================================================= */

    if (selectorPorPagina) {

        selectorPorPagina.addEventListener('change', () => {

            const valor = selectorPorPagina.value;

            LOGS_POR_PAGINA = valor === 'all'
                ? Infinity
                : Number(valor);

            paginaActual = 1;
            mostrarPagina();

        });

    }


    /* =========================================================
       14. ORDENACIÓN
       =========================================================
       La columna 4 (Fecha y hora) se ordena por instante real
       (data-fecha-orden, "aaaa-mm-dd HH:ii:ss"), no como texto:
       "31/01/2026" ordenado como texto quedaría antes que
       "05/02/2026" porque "3" < "5", aunque sea posterior.
    ========================================================= */

    botonesOrden.forEach(boton => {

        boton.addEventListener('click', () => {

            const columna = Number(boton.dataset.column);

            let direccion = boton.dataset.direction || 'none';

            if (direccion === 'none') {
                direccion = 'asc';
            } else if (direccion === 'asc') {
                direccion = 'desc';
            } else {
                direccion = 'none';
            }

            botonesOrden.forEach(otro => {

                if (otro !== boton) {
                    otro.dataset.direction = 'none';
                    otro.textContent = '↕';
                }

            });

            if (direccion === 'none') {

                boton.dataset.direction = 'none';
                boton.textContent = '↕';

                filas.sort((a, b) =>
                    Number(a.dataset.originalOrder) -
                    Number(b.dataset.originalOrder)
                );

            } else {

                boton.dataset.direction = direccion;
                boton.textContent = direccion === 'asc' ? '↑' : '↓';

                filas.sort((a, b) => {

                    const valorA = columna === 4
                        ? (a.dataset.fechaOrden || '')
                        : obtenerTextoCelda(a, columna);

                    const valorB = columna === 4
                        ? (b.dataset.fechaOrden || '')
                        : obtenerTextoCelda(b, columna);

                    if (valorA < valorB) {
                        return direccion === 'asc' ? -1 : 1;
                    }

                    if (valorA > valorB) {
                        return direccion === 'asc' ? 1 : -1;
                    }

                    return 0;

                });

            }

            filas.forEach(fila => {
                tbody.appendChild(fila);
            });

            paginaActual = 1;
            mostrarPagina();

        });

    });


    /* =========================================================
       15. PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
    ========================================================= */

    const btnToggleFiltros = document.getElementById('btnToggleFiltros');
    const panelFiltros = document.getElementById('panelFiltrosLogs');

    if (btnToggleFiltros && panelFiltros) {

        btnToggleFiltros.addEventListener('click', () => {

            const abierto = panelFiltros.style.display !== 'none';

            panelFiltros.style.display = abierto ? 'none' : 'block';

            btnToggleFiltros.setAttribute(
                'aria-expanded',
                abierto ? 'false' : 'true'
            );

        });

    }


    /* =========================================================
       16. VENTANA DE DETALLE
       Al pulsar una fila (escritorio o móvil) se abre la
       ventana con toda la información del log, con el mismo
       estilo que las de Clientes/Usuarios/Empresas.
    ========================================================= */

    function claseBadgeLog(tipo) {

        switch (tipo) {
            case 'Éxito':
                return 'log-success';
            case 'Información':
                return 'log-info';
            case 'Advertencia':
                return 'log-warning';
            case 'Error':
                return 'log-error';
            default:
                return 'log-info';
        }

    }

    const modalDetalle = document.getElementById('modalDetalleLog');
    const detalleAvatar = document.getElementById('detalleLogAvatar');
    const detalleEvento = document.getElementById('detalleLogEvento');
    const detalleFecha = document.getElementById('detalleLogFecha');
    const detalleEventoGrid = document.getElementById('detalleLogEventoGrid');
    const detalleFechaGrid = document.getElementById('detalleLogFechaGrid');
    const detalleTipo = document.getElementById('detalleLogTipo');
    const detalleUsuario = document.getElementById('detalleLogUsuario');
    const detalleDescripcion = document.getElementById('detalleLogDescripcion');
    const detalleIp = document.getElementById('detalleLogIp');

    function abrirModalDetalleLog(fila) {

        const datos = fila.dataset;

        if (detalleEvento) {
            detalleEvento.textContent = datos.evento || '';
        }

        if (detalleFecha) {
            detalleFecha.textContent = datos.fecha || '';
        }

        if (detalleEventoGrid) {
            detalleEventoGrid.textContent = datos.evento || '';
        }

        if (detalleFechaGrid) {
            detalleFechaGrid.textContent = datos.fecha || '';
        }

        if (detalleAvatar) {
            detalleAvatar.className = 'modal-detalle-avatar log-avatar ' + claseBadgeLog(datos.tipo);
            detalleAvatar.textContent = (datos.tipo || '').charAt(0);
        }

        if (detalleTipo) {
            detalleTipo.className = 'log-badge ' + claseBadgeLog(datos.tipo);
            detalleTipo.textContent = datos.tipo || '';
        }

        if (detalleUsuario) {
            detalleUsuario.textContent = datos.usuario || '';
        }

        if (detalleDescripcion) {
            detalleDescripcion.textContent = datos.descripcion || '';
        }

        if (detalleIp) {
            detalleIp.textContent = datos.ip || '';
        }

        if (modalDetalle) {
            modalDetalle.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    }

    window.cerrarModalDetalleLog = function () {

        if (modalDetalle) {
            modalDetalle.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    filas.forEach(fila => {

        fila.addEventListener('click', () => {
            abrirModalDetalleLog(fila);
        });

    });

    if (modalDetalle) {

        modalDetalle.addEventListener('click', event => {

            if (event.target === modalDetalle) {
                window.cerrarModalDetalleLog();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (
            event.key === 'Escape' &&
            modalDetalle &&
            modalDetalle.style.display !== 'none'
        ) {
            window.cerrarModalDetalleLog();
        }

    });


    /* =========================================================
       17. EXPORTAR PDF
       Exporta los logs que cumplen los filtros activos
       (ver js/exportar-pdf.js).
    ========================================================= */

    const btnExportarPDF = document.getElementById('btnExportarPDF');

    if (btnExportarPDF) {

        btnExportarPDF.addEventListener('click', () => {
            exportarListadoPDF('exportar_pdf.php', obtenerFilasFiltradas);
        });

    }


    /* =========================================================
       18. PAGINACIÓN INICIAL
    ========================================================= */

    memoriaFiltros.restaurar();
    mostrarPagina();

});

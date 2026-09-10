document.addEventListener('DOMContentLoaded', () => {


    /* =========================================================
       01. ELEMENTOS DEL DOM
    ========================================================= */

    const lista =
        document.querySelector('.incidencias-list');


    const partes =
        lista
            ? Array.from(
                lista.querySelectorAll('.incidencia-item')
            )
            : [];


    const buscarParte =
        document.getElementById('buscarParte');


    const filtroEstado =
        document.getElementById('filtroEstado');


    const filtroTipo =
        document.getElementById('filtroTipo');


    const filtroResponsable =
        document.getElementById('filtroResponsable');


    const filtroPrioridad =
        document.getElementById('filtroPrioridad');


    const selectorPorPagina =
        document.getElementById('partesPorPagina');


    const mostrando =
        document.getElementById('partesMostrando');


    const botonesPaginacion =
        document.querySelectorAll(
            '.incidencias-pagination .pagination-button'
        );


    let partesPorPagina = 10;

    let paginaActual = 1;



    /* =========================================================
       02. BOTONES DE FILTRO
    ========================================================= */

    const botonesFiltro =
        document.querySelectorAll(
            '.incidencias-filter-row .filter-actions button'
        );


    const btnBuscar =
        botonesFiltro[0] || null;


    const btnLimpiar =
        botonesFiltro[1] || null;



    /* =========================================================
       03. COMPROBACIÓN
    ========================================================= */

    if (!lista) {

        console.error(
            'No se encontró .incidencias-list'
        );

        return;

    }



    /* =========================================================
       04. TARJETAS DE RESUMEN
    ========================================================= */

    const tarjetasResumen =
        document.querySelectorAll(
            '.dashboard-cards .dashboard-card'
        );


    const tarjetaTotal =
        tarjetasResumen[0] || null;


    const tarjetaPendientes =
        tarjetasResumen[1] || null;


    const tarjetaEnCurso =
        tarjetasResumen[2] || null;


    const tarjetaCerrados =
        tarjetasResumen[3] || null;



    /* =========================================================
       05. NORMALIZAR TEXTO
    ========================================================= */

    function normalizar(texto) {

        return String(texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

    }



    /* =========================================================
       06. OBTENER INFORMACIÓN DEL PARTE
    ========================================================= */

    function obtenerTitulo(parte) {

        const elemento =
            parte.querySelector(
                '.incidencia-title strong'
            );


        return elemento
            ? elemento.textContent.trim()
            : '';

    }



    function obtenerId(parte) {

        const elemento =
            parte.querySelector(
                '.incidencia-id'
            );


        return elemento
            ? elemento.textContent.trim()
            : '';

    }



    function obtenerDescripcion(parte) {

        const elemento =
            parte.querySelector(
                '.incidencia-main > p'
            );


        return elemento
            ? elemento.textContent.trim()
            : '';

    }



    function obtenerResponsable(parte) {

        const elementos =
            parte.querySelectorAll(
                '.incidencia-meta span'
            );


        return elementos[1]
            ? elementos[1].textContent.trim()
            : '';

    }



    function obtenerCliente(parte) {

        const elementos =
            parte.querySelectorAll(
                '.incidencia-meta span'
            );


        if (!elementos[0]) {
            return '';
        }


        return elementos[0]
            .textContent
            .replace(/^Cliente:\s*/i, '')
            .trim();

    }



    function obtenerEstado(parte) {

        const elemento =
            parte.querySelector(
                '.status-badge'
            );


        return elemento
            ? elemento.textContent.trim()
            : '';

    }



    function obtenerPrioridad(parte) {

        const elemento =
            parte.querySelector(
                '.priority-badge'
            );


        return elemento
            ? elemento.textContent.trim()
            : '';

    }



    function obtenerTipo(parte) {

        const titulo =
            normalizar(
                obtenerTitulo(parte)
            );


        if (
            titulo.includes('instalacion') ||
            titulo.includes('alta')
        ) {
            return 'instalacion';
        }


        if (
            titulo.includes('mantenimiento')
        ) {
            return 'mantenimiento';
        }


        if (
            titulo.includes('revision') ||
            titulo.includes('comprobacion') ||
            titulo.includes('verificacion')
        ) {
            return 'revision';
        }


        if (
            titulo.includes('visita')
        ) {
            return 'visita';
        }


        if (
            titulo.includes('averia')
        ) {
            return 'averia';
        }


        return '';

    }



    /* =========================================================
       07. OBTENER PARTES FILTRADOS
    ========================================================= */

    function obtenerPartesFiltrados() {

        const texto =
            buscarParte
                ? normalizar(
                    buscarParte.value
                )
                : '';


        const estado =
            filtroEstado
                ? normalizar(
                    filtroEstado.value
                )
                : '';


        const tipo =
            filtroTipo
                ? normalizar(
                    filtroTipo.value
                )
                : '';


        const responsable =
            filtroResponsable
                ? normalizar(
                    filtroResponsable.value
                )
                : '';


        const prioridad =
            filtroPrioridad
                ? normalizar(
                    filtroPrioridad.value
                )
                : '';



        return partes.filter(
            parte => {


                /* ---------------------------------------------
                   BÚSQUEDA GENERAL
                --------------------------------------------- */

                if (texto !== '') {

                    const titulo =
                        normalizar(
                            obtenerTitulo(parte)
                        );


                    const cliente =
                        normalizar(
                            obtenerCliente(parte)
                        );


                    const descripcion =
                        normalizar(
                            obtenerDescripcion(parte)
                        );


                    const id =
                        normalizar(
                            obtenerId(parte)
                        );


                    const coincideTexto =
                        titulo.includes(texto) ||
                        cliente.includes(texto) ||
                        descripcion.includes(texto) ||
                        id.includes(texto);


                    if (!coincideTexto) {
                        return false;
                    }

                }



                /* ---------------------------------------------
                   ESTADO
                --------------------------------------------- */

                if (
                    estado !== '' &&
                    estado !== 'todos los estados'
                ) {

                    if (
                        normalizar(
                            obtenerEstado(parte)
                        ) !== estado
                    ) {

                        return false;

                    }

                }



                /* ---------------------------------------------
                   TIPO
                --------------------------------------------- */

                if (
                    tipo !== '' &&
                    tipo !== 'todos los tipos'
                ) {

                    if (
                        obtenerTipo(parte) !== tipo
                    ) {

                        return false;

                    }

                }



                /* ---------------------------------------------
                   RESPONSABLE
                --------------------------------------------- */

                if (
                    responsable !== '' &&
                    responsable !== 'todos'
                ) {

                    if (
                        normalizar(
                            obtenerResponsable(parte)
                        ) !== responsable
                    ) {

                        return false;

                    }

                }


                /* ---------------------------------------------
                   PRIORIDAD
                --------------------------------------------- */

                if (
                    prioridad !== '' &&
                    prioridad !== 'todas las prioridades'
                ) {

                    if (
                        normalizar(
                            obtenerPrioridad(parte)
                        ) !== prioridad
                    ) {

                        return false;

                    }

                }


                return true;

            }
        );

    }



    /* =========================================================
       08. MOSTRAR / OCULTAR PARTES
    ========================================================= */

    function mostrarPartes() {

        const filtrados =
            obtenerPartesFiltrados();


        const totalPaginas =
            obtenerTotalPaginas(filtrados);


        paginaActual =
            Math.min(
                paginaActual,
                totalPaginas
            );


        const inicio =
            partesPorPagina === Infinity
                ? 0
                : (paginaActual - 1) *
                    partesPorPagina;


        const fin =
            partesPorPagina === Infinity
                ? filtrados.length
                : inicio + partesPorPagina;


        const partesPagina =
            filtrados.slice(
                inicio,
                fin
            );



        partes.forEach(
            parte => {

                parte.style.display =
                    partesPagina.includes(parte)
                        ? ''
                        : 'none';

            }
        );



        actualizarResumen(filtrados);

        actualizarContadores(filtrados);

        actualizarPaginacion(totalPaginas);

    }



    /* =========================================================
       09. CONTADOR
    ========================================================= */

    function actualizarContadores(filtrados) {

        if (!mostrando) {
            return;
        }


        if (filtrados.length === 0) {

            mostrando.textContent =
                'Mostrando 0 de 0 partes';

            return;

        }


        if (partesPorPagina === Infinity) {

            mostrando.textContent =
                `Mostrando ${filtrados.length} de ${filtrados.length} partes`;

            return;

        }


        const inicio =
            (paginaActual - 1) *
                partesPorPagina + 1;


        const fin =
            Math.min(
                inicio + partesPorPagina - 1,
                filtrados.length
            );


        mostrando.textContent =
            `Mostrando ${inicio}-${fin} de ${filtrados.length} partes`;

    }



    /* =========================================================
       10. TOTAL DE PÁGINAS
    ========================================================= */

    function obtenerTotalPaginas(filtrados) {

        if (filtrados.length === 0) {
            return 1;
        }


        return partesPorPagina === Infinity
            ? 1
            : Math.ceil(
                filtrados.length /
                partesPorPagina
            );

    }



    /* =========================================================
       11. ACTUALIZAR PAGINACIÓN
    ========================================================= */

    function actualizarPaginacion(totalPaginas) {

        botonesPaginacion.forEach(
            boton => {

                const accion =
                    boton.dataset.page;


                if (accion === 'prev') {

                    boton.disabled =
                        paginaActual <= 1;


                    boton.classList.toggle(
                        'disabled',
                        paginaActual <= 1
                    );


                    return;

                }


                if (accion === 'next') {

                    boton.disabled =
                        paginaActual >= totalPaginas;


                    boton.classList.toggle(
                        'disabled',
                        paginaActual >= totalPaginas
                    );


                    return;

                }


                const numeroPagina =
                    Number(accion);


                if (!Number.isNaN(numeroPagina)) {

                    boton.style.display =
                        partesPorPagina === Infinity ||
                        numeroPagina > totalPaginas
                            ? 'none'
                            : 'inline-flex';


                    boton.classList.toggle(
                        'active',
                        numeroPagina === paginaActual
                    );

                }

            }
        );

    }



    /* =========================================================
       12. ACTUALIZAR RESUMEN
    ========================================================= */

    function actualizarResumen(filtrados) {

        const total =
            filtrados.length;


        const pendientes =
            filtrados.filter(
                parte =>
                    normalizar(
                        obtenerEstado(parte)
                    ) === 'pendiente'
            ).length;


        const enCurso =
            filtrados.filter(
                parte =>
                    normalizar(
                        obtenerEstado(parte)
                    ) === 'en curso'
            ).length;


        const cerrados =
            filtrados.filter(
                parte => {

                    const estado =
                        normalizar(
                            obtenerEstado(parte)
                        );


                    return (
                        estado === 'cerrado' ||
                        estado === 'completado'
                    );

                }
            ).length;



        actualizarNumeroTarjeta(
            tarjetaTotal,
            total
        );


        actualizarNumeroTarjeta(
            tarjetaPendientes,
            pendientes
        );


        actualizarNumeroTarjeta(
            tarjetaEnCurso,
            enCurso
        );


        actualizarNumeroTarjeta(
            tarjetaCerrados,
            cerrados
        );

    }



    function actualizarNumeroTarjeta(
        tarjeta,
        numero
    ) {

        if (!tarjeta) {
            return;
        }


        const numeroElemento =
            tarjeta.querySelector(
                '.card-info strong'
            );


        if (numeroElemento) {

            numeroElemento.textContent =
                numero;

        }

    }



    /* =========================================================
       13. BOTÓN BUSCAR
    ========================================================= */

    if (btnBuscar) {

        btnBuscar.addEventListener(
            'click',
            () => {

                paginaActual = 1;

                mostrarPartes();

            }
        );

    }



    /* =========================================================
       14. BÚSQUEDA EN TIEMPO REAL
    ========================================================= */

    if (buscarParte) {

        buscarParte.addEventListener(
            'input',
            () => {

                paginaActual = 1;

                mostrarPartes();

            }
        );

    }



    /* =========================================================
       15. CAMBIO DE FILTROS
    ========================================================= */

    [
        filtroEstado,
        filtroTipo,
        filtroResponsable,
        filtroPrioridad

    ].forEach(
        filtro => {

            if (!filtro) {
                return;
            }


            filtro.addEventListener(
                'change',
                () => {

                    paginaActual = 1;

                    mostrarPartes();

                }
            );

        }
    );



    /* =========================================================
       16. PAGINACIÓN
    ========================================================= */

    botonesPaginacion.forEach(
        boton => {

            boton.addEventListener(
                'click',
                () => {

                    const accion =
                        boton.dataset.page;


                    if (
                        accion === 'prev' &&
                        paginaActual > 1
                    ) {

                        paginaActual--;

                    }


                    if (accion === 'next') {

                        const totalPaginas =
                            obtenerTotalPaginas(
                                obtenerPartesFiltrados()
                            );


                        if (
                            paginaActual <
                            totalPaginas
                        ) {

                            paginaActual++;

                        }

                    }


                    const numeroPagina =
                        Number(accion);


                    if (
                        !Number.isNaN(
                            numeroPagina
                        )
                    ) {

                        paginaActual =
                            numeroPagina;

                    }


                    mostrarPartes();

                }
            );

        }
    );



    /* =========================================================
       17. PARTES POR PÁGINA
    ========================================================= */

    if (selectorPorPagina) {

        selectorPorPagina.addEventListener(
            'change',
            () => {

                partesPorPagina =
                    selectorPorPagina.value === 'all'
                        ? Infinity
                        : Number(
                            selectorPorPagina.value
                        );


                paginaActual = 1;

                mostrarPartes();

            }
        );

    }



    /* =========================================================
       18. LIMPIAR FILTROS
    ========================================================= */

    if (btnLimpiar) {

        btnLimpiar.addEventListener(
            'click',
            () => {

                if (buscarParte) {

                    buscarParte.value =
                        '';

                }


                if (filtroEstado) {

                    filtroEstado.selectedIndex =
                        0;

                }


                if (filtroTipo) {

                    filtroTipo.selectedIndex =
                        0;

                }


                if (filtroResponsable) {

                    filtroResponsable.selectedIndex =
                        0;

                }


                if (filtroPrioridad) {

                    filtroPrioridad.selectedIndex =
                        0;

                }


                paginaActual = 1;

                mostrarPartes();

            }
        );

    }



    /* =========================================================
       19. CLIC EN UN PARTE
    ========================================================= */

    partes.forEach(
        parte => {

            parte.style.cursor =
                'pointer';


            parte.addEventListener(
                'click',
                evento => {

                    if (
                        evento.target.closest(
                            'button, a, input, select'
                        )
                    ) {

                        return;

                    }


                    const id =
                        obtenerId(parte);


                    console.log(
                        'Parte seleccionado:',
                        id
                    );


                    /*
                     * Posteriormente:
                     *
                     * window.location.href =
                     *     'parte.php?id=2048';
                     */

                }
            );

        }
    );



    /* =========================================================
       20. BOTÓN "+ NUEVO PARTE"
    ========================================================= */

    const btnNuevoParte =
        document.querySelector(
            '.page-header-actions .config-save-button'
        );


    if (btnNuevoParte) {

        btnNuevoParte.addEventListener(
            'click',
            () => {

                /*
                 * Posteriormente:
                 *
                 * window.location.href =
                 *     'nuevo-parte.php';
                 */

                console.log(
                    'Nuevo parte'
                );

            }
        );

    }



    /* =========================================================
       21. INICIALIZACIÓN
    ========================================================= */

    mostrarPartes();


});


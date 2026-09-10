document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       01. ELEMENTOS DEL DOM
       Referencias a los elementos que el script necesita
       leer o modificar.
    ========================================================= */

    const tbody = document.getElementById('clientesBody');
    const tabla = document.querySelector('.clientes-table');

    const contador = document.getElementById('clientesContador');
    const mostrando = document.getElementById('clientesMostrando');

    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    const selectorPorPagina = document.getElementById('selectorPorPagina');

    const filtros = document.querySelectorAll('.column-filter');

    const botonesOrden = document.querySelectorAll(
        '.clientes-table .sort-button'
    );

    const botonesPaginacion = document.querySelectorAll(
        '.clientes-pagination .pagination-button'
    );


    /* =========================================================
       02. COMPROBACIÓN DE ELEMENTOS
       Si falta algo esencial (tabla o cuerpo de la tabla),
       detenemos la ejecución para evitar errores en cascada.
    ========================================================= */

    if (!tbody || !tabla) {
        console.error(
            'No se encontró #clientesBody o .clientes-table'
        );

        return;
    }


    /* =========================================================
       03. ESTADO Y CONFIGURACIÓN
       Variables que controlan la paginación y el orden.
       CLIENTES_POR_PAGINA es "let" porque el selector
       puede cambiarla en tiempo de ejecución (5, 10 o Infinity
       para "todos").
    ========================================================= */

    const CLIENTES_TOTALES = 186;

    let CLIENTES_POR_PAGINA = 5;

    let paginaActual = 1;

    let filas = Array.from(
        tbody.querySelectorAll('tr')
    );


    /* =========================================================
       04. ORDEN ORIGINAL
       Guardamos la posición inicial de cada cliente para
       poder volver a este orden cuando se desactive
       la ordenación de una columna.
    ========================================================= */

    filas.forEach((fila, index) => {
        fila.dataset.originalOrder = index;
    });


    /* =========================================================
       05. FUNCIONES AUXILIARES DE TEXTO
       Normalizan el contenido de las celdas para que las
       búsquedas y comparaciones ignoren mayúsculas y acentos.
    ========================================================= */

    function normalizar(texto) {

        return String(texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

    }


    function obtenerTextoCelda(fila, columna) {

        const celdas = fila.querySelectorAll('td');

        if (!celdas[columna]) {
            return '';
        }

        return normalizar(
            celdas[columna].textContent
        );

    }


    /* =========================================================
       06. FILTRADO
       Devuelve solo las filas que cumplen TODOS los filtros
       de columna activos (texto o select).
    ========================================================= */

    function obtenerFilasFiltradas() {

        return filas.filter(fila => {

            let coincide = true;

            filtros.forEach(filtro => {

                const columna = Number(
                    filtro.dataset.column
                );

                const valorFiltro = normalizar(
                    filtro.value
                );

                /*
                 * Si el filtro está vacío,
                 * no se aplica.
                 */
                if (valorFiltro === '') {
                    return;
                }


                const valorCelda = obtenerTextoCelda(
                    fila,
                    columna
                );


                /*
                 * El texto de la celda debe contener
                 * el valor introducido/seleccionado.
                 */
                if (!valorCelda.includes(valorFiltro)) {
                    coincide = false;
                }

            });

            return coincide;

        });

    }


    /* =========================================================
       07. CÁLCULO Y RENDERIZADO DE PÁGINA
       obtenerTotalPaginas() calcula cuántas páginas resultan
       del número de filas filtradas y del tamaño de página
       actual (CLIENTES_POR_PAGINA puede ser Infinity si se
       eligió "Todos", lo que da siempre 1 página).

       mostrarPagina() oculta todas las filas y vuelve a
       mostrar únicamente las que corresponden a la página
       y al tamaño de página actuales.
    ========================================================= */

    function obtenerTotalPaginas(filasFiltradas) {

        if (filasFiltradas.length === 0) {
            return 1;
        }

        return Math.ceil(
            filasFiltradas.length /
            CLIENTES_POR_PAGINA
        );

    }


    function mostrarPagina() {

        const filasFiltradas = obtenerFilasFiltradas();

        const totalPaginas = obtenerTotalPaginas(filasFiltradas);

        /*
         * Evitamos que la página actual
         * quede fuera del rango.
         */
        if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        if (paginaActual < 1) {
            paginaActual = 1;
        }


        /*
         * Primero ocultamos todas las filas.
         */
        filas.forEach(fila => {
            fila.style.display = 'none';
        });


        /*
         * Calculamos qué filas corresponden a la página
         * actual. Si CLIENTES_POR_PAGINA es Infinity
         * ("Todos"), el corte final es el total de filas
         * filtradas, es decir, no hay corte.
         */
        const inicio = (paginaActual - 1) * CLIENTES_POR_PAGINA;

        const fin = CLIENTES_POR_PAGINA === Infinity
            ? filasFiltradas.length
            : inicio + CLIENTES_POR_PAGINA;

        const filasPagina = filasFiltradas.slice(inicio, fin);


        /*
         * Mostramos solamente
         * las filas de la página actual.
         */
        filasPagina.forEach(fila => {
            fila.style.display = '';
        });


        actualizarContadores(filasFiltradas, totalPaginas);
        actualizarPaginacion(totalPaginas);

    }


    /* =========================================================
       08. CONTADORES
       Actualiza el texto "X clientes encontrados" y el
       rango "Mostrando X-Y de Z clientes".
    ========================================================= */

    function actualizarContadores(
        filasFiltradas,
        totalPaginas
    ) {

        const cantidadFiltrada =
            filasFiltradas.length;


        /*
         * Contador superior del panel.
         */
        if (contador) {

            if (cantidadFiltrada === 1) {

                contador.textContent =
                    '1 cliente encontrado';

            } else {

                contador.textContent =
                    `${cantidadFiltrada} clientes encontrados`;

            }

        }


        /*
         * Rango que se está mostrando.
         */
        if (mostrando) {

            if (cantidadFiltrada === 0) {

                mostrando.textContent =
                    `Mostrando 0 de ${CLIENTES_TOTALES} clientes`;

                return;
            }


            /*
             * Si está seleccionado "Todos"
             * (CLIENTES_POR_PAGINA = Infinity),
             * se muestran todas las filas filtradas
             * de una vez, sin rango de página.
             */
            if (CLIENTES_POR_PAGINA === Infinity) {

                mostrando.textContent =
                    `Mostrando ${cantidadFiltrada} de ${cantidadFiltrada} clientes`;

                return;
            }


            const inicio =
                (paginaActual - 1) *
                CLIENTES_POR_PAGINA +
                1;


            const fin =
                Math.min(
                    inicio +
                    CLIENTES_POR_PAGINA -
                    1,
                    cantidadFiltrada
                );


            mostrando.textContent =
                `Mostrando ${inicio}-${fin} de ${cantidadFiltrada} clientes`;

        }

    }


    /* =========================================================
       09. ACTUALIZAR BOTONES DE PAGINACIÓN
       Activa/desactiva "anterior" y "siguiente" según la
       página actual, y muestra/oculta u marca como activo
       cada botón numérico. Cuando CLIENTES_POR_PAGINA es
       Infinity ("Todos") no tiene sentido mostrar botones
       numéricos, así que se ocultan todos.
    ========================================================= */

    function actualizarPaginacion(
        totalPaginas
    ) {

        botonesPaginacion.forEach(
            boton => {

                const accion =
                    boton.dataset.page;


                /* -------------------------------------------------
                   BOTÓN ANTERIOR
                ------------------------------------------------- */

                if (accion === 'prev') {

                    boton.disabled =
                        paginaActual <= 1;

                    boton.classList.toggle(
                        'disabled',
                        paginaActual <= 1
                    );

                    return;
                }


                /* -------------------------------------------------
                   BOTÓN SIGUIENTE
                ------------------------------------------------- */

                if (accion === 'next') {

                    boton.disabled =
                        paginaActual >= totalPaginas;

                    boton.classList.toggle(
                        'disabled',
                        paginaActual >= totalPaginas
                    );

                    return;
                }


                /* -------------------------------------------------
                   BOTONES NUMÉRICOS

                   Se ocultan si "Todos" está seleccionado
                   o si su número supera el total de páginas.
                ------------------------------------------------- */

                const numeroPagina =
                    Number(accion);


                if (!Number.isNaN(numeroPagina)) {

                    boton.style.display =
                        (CLIENTES_POR_PAGINA === Infinity || numeroPagina > totalPaginas)
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
       10. EVENTOS DE PAGINACIÓN
       Gestiona los clics en "anterior", "siguiente" y en
       cada botón numérico.
    ========================================================= */

    botonesPaginacion.forEach(
        boton => {

            boton.addEventListener(
                'click',
                () => {

                    const accion =
                        boton.dataset.page;


                    /* -------------------------------------------------
                       ANTERIOR
                    ------------------------------------------------- */

                    if (accion === 'prev') {

                        if (paginaActual > 1) {

                            paginaActual--;

                            mostrarPagina();

                        }

                        return;
                    }


                    /* -------------------------------------------------
                       SIGUIENTE
                    ------------------------------------------------- */

                    if (accion === 'next') {

                        const filasFiltradas =
                            obtenerFilasFiltradas();


                        const totalPaginas =
                            obtenerTotalPaginas(
                                filasFiltradas
                            );


                        if (
                            paginaActual <
                            totalPaginas
                        ) {

                            paginaActual++;

                            mostrarPagina();

                        }

                        return;
                    }


                    /* -------------------------------------------------
                       PÁGINA NUMÉRICA
                    ------------------------------------------------- */

                    const numeroPagina =
                        Number(accion);


                    if (
                        !Number.isNaN(
                            numeroPagina
                        )
                    ) {

                        paginaActual =
                            numeroPagina;

                        mostrarPagina();

                    }

                }
            );

        }
    );


    /* =========================================================
       11. EVENTOS DE FILTROS
       Cada vez que cambia un filtro (texto o select),
       volvemos a la página 1 y recalculamos la vista.
    ========================================================= */

    filtros.forEach(filtro => {

        filtro.addEventListener(
            'input',
            () => {

                paginaActual = 1;

                mostrarPagina();

            }
        );


        filtro.addEventListener(
            'change',
            () => {

                paginaActual = 1;

                mostrarPagina();

            }
        );

    });


    /* =========================================================
       12. LIMPIAR FILTROS
       Vacía todos los inputs/selects de filtro y vuelve
       a la primera página.
    ========================================================= */

    if (btnLimpiar) {

        btnLimpiar.addEventListener(
            'click',
            () => {

                filtros.forEach(
                    filtro => {

                        filtro.value = '';

                    }
                );


                paginaActual = 1;

                mostrarPagina();

            }
        );

    }


    /* =========================================================
       13. EVENTO DEL SELECTOR "MOSTRAR X CLIENTES"
       Cambia CLIENTES_POR_PAGINA según la opción elegida
       (5, 10 o Infinity para "todos") y vuelve a la página 1.
    ========================================================= */

    if (selectorPorPagina) {

        selectorPorPagina.addEventListener('change', () => {

            const valor = selectorPorPagina.value;

            CLIENTES_POR_PAGINA = valor === 'todos'
                ? Infinity
                : Number(valor);

            paginaActual = 1;

            mostrarPagina();

        });

    }


    /* =========================================================
       14. ORDENACIÓN DE COLUMNAS
       Cicla cada botón de orden entre: sin ordenar -> ascendente
       -> descendente -> sin ordenar, reordena el array "filas"
       y lo vuelve a insertar en el tbody según ese orden.
    ========================================================= */

    botonesOrden.forEach(
        boton => {

            boton.addEventListener(
                'click',
                () => {

                    const columna =
                        Number(
                            boton.dataset.column
                        );


                    let direccion =
                        boton.dataset.direction ||
                        'none';


                    /* -------------------------------------------------
                       CICLO DE ORDENACIÓN

                       none -> asc -> desc -> none
                    ------------------------------------------------- */

                    if (
                        direccion ===
                        'none'
                    ) {

                        direccion = 'asc';

                    } else if (
                        direccion ===
                        'asc'
                    ) {

                        direccion = 'desc';

                    } else {

                        direccion = 'none';

                    }


                    /*
                     * Reiniciamos los demás botones.
                     */
                    botonesOrden.forEach(
                        otro => {

                            if (
                                otro !== boton
                            ) {

                                otro.dataset.direction =
                                    'none';

                                otro.textContent =
                                    '↕';

                            }

                        }
                    );


                    /* -------------------------------------------------
                       ORDEN ORIGINAL
                    ------------------------------------------------- */

                    if (
                        direccion ===
                        'none'
                    ) {

                        boton.dataset.direction =
                            'none';

                        boton.textContent =
                            '↕';


                        filas.sort(
                            (a, b) =>
                                Number(
                                    a.dataset.originalOrder
                                ) -
                                Number(
                                    b.dataset.originalOrder
                                )
                        );

                    }


                    /* -------------------------------------------------
                       ORDEN ASCENDENTE / DESCENDENTE
                    ------------------------------------------------- */

                    else {

                        boton.dataset.direction =
                            direccion;


                        boton.textContent =
                            direccion === 'asc'
                                ? '↑'
                                : '↓';


                        filas.sort(
                            (a, b) => {

                                const valorA =
                                    obtenerTextoCelda(
                                        a,
                                        columna
                                    );

                                const valorB =
                                    obtenerTextoCelda(
                                        b,
                                        columna
                                    );


                                if (
                                    valorA <
                                    valorB
                                ) {

                                    return direccion ===
                                        'asc'
                                        ? -1
                                        : 1;

                                }


                                if (
                                    valorA >
                                    valorB
                                ) {

                                    return direccion ===
                                        'asc'
                                        ? 1
                                        : -1;

                                }


                                return 0;

                            }
                        );

                    }


                    /*
                     * Volvemos a insertar las filas
                     * en el tbody según el nuevo orden.
                     */
                    filas.forEach(
                        fila => {

                            tbody.appendChild(
                                fila
                            );

                        }
                    );


                    /*
                     * Después de ordenar volvemos
                     * a la primera página.
                     */
                    paginaActual = 1;

                    mostrarPagina();

                }
            );

        }
    );


    /* =========================================================
       15. PAGINACIÓN INICIAL
       Pinta la tabla en cuanto el DOM está listo.
    ========================================================= */

    mostrarPagina();

});
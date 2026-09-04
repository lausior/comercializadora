document.addEventListener('DOMContentLoaded', () => {

    const tabla = document.querySelector('.clientes-table');

    if (!tabla) {
        return;
    }


    const tbody = tabla.querySelector('tbody');
    const filasOriginales = Array.from(tbody.querySelectorAll('tr'));


    /*
    ========================================
    ELEMENTOS DE FILTRADO
    ========================================
    */

    const buscarCliente = document.getElementById('buscarCliente');
    const filtroEstado = document.getElementById('filtroEstadoCliente');
    const filtroComercializadora = document.getElementById('filtroComercializadora');
    const filtroTarifa = document.getElementById('filtroTarifa');


    /*
    ========================================
    BOTONES
    ========================================
    */

    const botonesOrden = tabla.querySelectorAll(
        '.sort-button:not(.sort-disabled)'
    );


    const botonesFiltro = document.querySelectorAll(
        '.clientes-filter-row .config-save-button'
    );


    const botonLimpiar = document.querySelector(
        '.clientes-filter-row .panel-action'
    );


    /*
    ========================================
    CONTADOR
    ========================================
    */

    const contadorClientes = document.querySelector(
        '.clientes-table-panel .panel-header p'
    );


    const contadorPaginacion = document.querySelector(
        '.clientes-pagination > span'
    );


    /*
    ========================================
    FUNCIONES AUXILIARES
    ========================================
    */

    function normalizarTexto(texto) {

        return texto
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

    }


    function obtenerTextoCelda(fila, columna) {

        const celdas = fila.querySelectorAll('td');

        if (!celdas[columna]) {
            return '';
        }

        return celdas[columna]
            .textContent
            .replace(/\s+/g, ' ')
            .trim();

    }


    /*
    ========================================
    FILTRAR
    ========================================
    */

    function aplicarFiltros() {

        const textoBusqueda = normalizarTexto(
            buscarCliente ? buscarCliente.value : ''
        );


        const estadoSeleccionado = normalizarTexto(
            filtroEstado ? filtroEstado.value : 'Todos los estados'
        );


        const comercializadoraSeleccionada = normalizarTexto(
            filtroComercializadora
                ? filtroComercializadora.value
                : 'Todas'
        );


        const tarifaSeleccionada = normalizarTexto(
            filtroTarifa
                ? filtroTarifa.value
                : 'Todas'
        );


        let visibles = 0;


        filasOriginales.forEach(fila => {

            const cliente = normalizarTexto(
                obtenerTextoCelda(fila, 0)
            );


            const identificacion = normalizarTexto(
                obtenerTextoCelda(fila, 1)
            );


            const comercializadora = normalizarTexto(
                obtenerTextoCelda(fila, 2)
            );


            const tarifa = normalizarTexto(
                obtenerTextoCelda(fila, 3)
            );


            const estado = normalizarTexto(
                obtenerTextoCelda(fila, 5)
            );


            /*
            ================================
            BUSCADOR
            ================================
            */

            const coincideBusqueda =
                textoBusqueda === '' ||
                cliente.includes(textoBusqueda) ||
                identificacion.includes(textoBusqueda);


            /*
            ================================
            ESTADO
            ================================
            */

            const coincideEstado =
                estadoSeleccionado === '' ||
                estadoSeleccionado === 'todos los estados' ||
                estado.includes(estadoSeleccionado);


            /*
            ================================
            COMERCIALIZADORA
            ================================
            */

            const coincideComercializadora =
                comercializadoraSeleccionada === '' ||
                comercializadoraSeleccionada === 'todas' ||
                comercializadora === comercializadoraSeleccionada;


            /*
            ================================
            TARIFA
            ================================
            */

            const coincideTarifa =
                tarifaSeleccionada === '' ||
                tarifaSeleccionada === 'todas' ||
                tarifa.includes(tarifaSeleccionada);


            /*
            ================================
            RESULTADO
            ================================
            */

            const mostrar =
                coincideBusqueda &&
                coincideEstado &&
                coincideComercializadora &&
                coincideTarifa;


            fila.style.display = mostrar ? '' : 'none';


            if (mostrar) {
                visibles++;
            }

        });


        actualizarContadores(visibles);

    }


    /*
    ========================================
    CONTADORES
    ========================================
    */

    function actualizarContadores(cantidad) {

        if (contadorClientes) {

            contadorClientes.textContent =
                `${cantidad} clientes encontrados`;

        }


        if (contadorPaginacion) {

            contadorPaginacion.textContent =
                `Mostrando ${cantidad} de 186 clientes`;

        }

    }


    /*
    ========================================
    ORDENACIÓN
    ========================================
    */

    botonesOrden.forEach(boton => {

        boton.dataset.order = 'none';


        boton.addEventListener('click', () => {

            const columna = parseInt(
                boton.dataset.column,
                10
            );


            let orden;


            if (
                boton.dataset.order === 'none' ||
                boton.dataset.order === 'desc'
            ) {

                orden = 'asc';

            } else {

                orden = 'desc';

            }


            /*
            Reseteamos las demás flechas
            */

            botonesOrden.forEach(otroBoton => {

                if (otroBoton !== boton) {

                    otroBoton.dataset.order = 'none';
                    otroBoton.textContent = '↕';

                }

            });


            boton.dataset.order = orden;


            if (orden === 'asc') {

                boton.textContent = '↑';

            } else {

                boton.textContent = '↓';

            }


            ordenarTabla(columna, orden);

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

            const valorA = obtenerValorOrden(
                filaA,
                columna
            );


            const valorB = obtenerValorOrden(
                filaB,
                columna
            );


            let resultado = 0;


            /*
            FECHA
            */

            if (columna === 4) {

                resultado =
                    convertirFecha(valorA) -
                    convertirFecha(valorB);

            }


            /*
            TEXTO
            */

            else {

                resultado = valorA.localeCompare(
                    valorB,
                    'es',
                    {
                        sensitivity: 'base'
                    }
                );

            }


            return orden === 'asc'
                ? resultado
                : -resultado;

        });


        filas.forEach(fila => {

            tbody.appendChild(fila);

        });

    }


    /*
    ========================================
    OBTENER VALOR PARA ORDENACIÓN
    ========================================
    */

    function obtenerValorOrden(fila, columna) {

        const celdas = fila.querySelectorAll('td');


        if (!celdas[columna]) {
            return '';
        }


        return normalizarTexto(
            celdas[columna].textContent
        );

    }


    /*
    ========================================
    CONVERTIR FECHA
    ========================================
    */

    function convertirFecha(texto) {

        /*
        Formato:
        Hoy, 09:42
        Ayer, 17:42
        02/09/2026 · 15:20
        */

        const textoOriginal = texto.trim();


        /*
        FECHAS CON DÍA/MES/AÑO
        */

        const fecha = textoOriginal.match(
            /(\d{2})\/(\d{2})\/(\d{4}).*?(\d{2}):(\d{2})/
        );


        if (fecha) {

            const dia = parseInt(
                fecha[1],
                10
            );

            const mes = parseInt(
                fecha[2],
                10
            ) - 1;

            const anio = parseInt(
                fecha[3],
                10
            );

            const hora = parseInt(
                fecha[4],
                10
            );

            const minutos = parseInt(
                fecha[5],
                10
            );


            return new Date(
                anio,
                mes,
                dia,
                hora,
                minutos
            ).getTime();

        }


        /*
        HOY
        */

        const hoy = textoOriginal.match(
            /hoy,\s*(\d{2}):(\d{2})/i
        );


        if (hoy) {

            const fechaHoy = new Date();

            fechaHoy.setHours(
                parseInt(hoy[1], 10),
                parseInt(hoy[2], 10),
                0,
                0
            );


            return fechaHoy.getTime();

        }


        /*
        AYER
        */

        const ayer = textoOriginal.match(
            /ayer,\s*(\d{2}):(\d{2})/i
        );


        if (ayer) {

            const fechaAyer = new Date();

            fechaAyer.setDate(
                fechaAyer.getDate() - 1
            );


            fechaAyer.setHours(
                parseInt(ayer[1], 10),
                parseInt(ayer[2], 10),
                0,
                0
            );


            return fechaAyer.getTime();

        }


        /*
        SI NO ENCUENTRA FECHA
        */

        return 0;

    }


    /*
    ========================================
    BOTÓN BUSCAR
    ========================================
    */

    if (botonesFiltro.length > 0) {

        const botonBuscar =
            botonesFiltro[0];


        botonBuscar.addEventListener(
            'click',
            event => {

                event.preventDefault();

                aplicarFiltros();

            }
        );

    }


    /*
    ========================================
    FILTRADO AUTOMÁTICO
    ========================================
    */

    if (buscarCliente) {

        buscarCliente.addEventListener(
            'input',
            aplicarFiltros
        );

    }


    if (filtroEstado) {

        filtroEstado.addEventListener(
            'change',
            aplicarFiltros
        );

    }


    if (filtroComercializadora) {

        filtroComercializadora.addEventListener(
            'change',
            aplicarFiltros
        );

    }


    if (filtroTarifa) {

        filtroTarifa.addEventListener(
            'change',
            aplicarFiltros
        );

    }


    /*
    ========================================
    LIMPIAR FILTROS
    ========================================
    */

    if (botonLimpiar) {

        botonLimpiar.addEventListener(
            'click',
            event => {

                event.preventDefault();


                if (buscarCliente) {
                    buscarCliente.value = '';
                }


                if (filtroEstado) {
                    filtroEstado.selectedIndex = 0;
                }


                if (filtroComercializadora) {
                    filtroComercializadora.selectedIndex = 0;
                }


                if (filtroTarifa) {
                    filtroTarifa.selectedIndex = 0;
                }


                filasOriginales.forEach(fila => {
                    fila.style.display = '';
                });


                actualizarContadores(
                    filasOriginales.length
                );

            }
        );

    }


    /*
    ========================================
    ESTADO INICIAL
    ========================================
    */

    actualizarContadores(
        filasOriginales.length
    );

});
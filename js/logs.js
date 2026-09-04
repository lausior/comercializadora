document.addEventListener('DOMContentLoaded', () => {

    const table = document.querySelector('.logs-table');

    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    const filtroTipo = document.getElementById('tipo');
    const filtroUsuario = document.getElementById('usuario');
    const filtroFecha = document.getElementById('fecha');

    const botonFiltrar = document.querySelector('.logs-filters .logs-btn.primary');

    const sortButtons = table.querySelectorAll('.sort-button');

    /*
    ========================================
    FILTRADO
    ========================================
    */

    function aplicarFiltros() {

        const tipoSeleccionado = filtroTipo
            ? filtroTipo.value.toLowerCase().trim()
            : 'todos';

        const usuarioSeleccionado = filtroUsuario
            ? filtroUsuario.value.toLowerCase().trim()
            : 'todos';

        const fechaSeleccionada = filtroFecha
            ? filtroFecha.value
            : '';

        let registrosVisibles = 0;

        rows.forEach(row => {

            const celdas = row.querySelectorAll('td');

            if (celdas.length < 6) {
                return;
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
                tipoSeleccionado === 'todos' ||
                tipo === tipoSeleccionado;


            const coincideUsuario =
                usuarioSeleccionado === 'todos los usuarios' ||
                usuario === usuarioSeleccionado;


            const coincideFecha =
                fechaSeleccionada === '' ||
                fechaFila === fechaSeleccionada;


            const visible =
                coincideTipo &&
                coincideUsuario &&
                coincideFecha;


            row.style.display = visible ? '' : 'none';


            if (visible) {
                registrosVisibles++;
            }

        });


        actualizarContador(registrosVisibles);

    }


    /*
    ========================================
    CONTADOR
    ========================================
    */

    function actualizarContador(cantidad) {

        const contador = document.querySelector('.logs-footer > span');

        if (!contador) {
            return;
        }

        contador.textContent = `Mostrando ${cantidad} registros`;

    }


    /*
    ========================================
    BOTÓN FILTRAR
    ========================================
    */

    if (botonFiltrar) {

        botonFiltrar.addEventListener('click', (event) => {

            event.preventDefault();

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
            aplicarFiltros();
        });

    }


    if (filtroUsuario) {

        filtroUsuario.addEventListener('change', () => {
            aplicarFiltros();
        });

    }


    if (filtroFecha) {

        filtroFecha.addEventListener('change', () => {
            aplicarFiltros();
        });

    }


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

    actualizarContador(rows.length);

});
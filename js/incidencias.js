document.addEventListener('DOMContentLoaded', () => {


/* =========================================================
   01. ELEMENTOS DEL DOM
   Referencias a los elementos que necesita el script.
========================================================= */

const lista = document.querySelector('.incidencias-list');

const incidencias =
    lista
        ? Array.from(
            lista.querySelectorAll('.incidencia-item')
        )
        : [];

const buscarIncidencia =
    document.getElementById('buscarIncidencia');

const filtroEstado =
    document.getElementById('filtroEstado');

const filtroPrioridad =
    document.getElementById('filtroPrioridad');

const filtroResponsable =
    document.getElementById('filtroResponsable');

const selectorPorPagina =
    document.getElementById('incidenciasPorPagina');

const mostrando =
    document.getElementById('incidenciasMostrando');

const botonesPaginacion =
    document.querySelectorAll(
        '.incidencias-pagination .pagination-button'
    );

let incidenciasPorPagina = 10;

let paginaActual = 1;


/*
 * Botones de búsqueda y limpieza.
 *
 * Como en el HTML actual no tienen ID,
 * los localizamos dentro de .filter-actions.
 */

const botonesFiltro =
    document.querySelectorAll(
        '.incidencias-filter-row .filter-actions button'
    );

const btnBuscar =
    botonesFiltro[0] || null;

const btnLimpiar =
    botonesFiltro[1] || null;


/*
 * Botón "Ver todas".
 */

const btnVerTodas =
    document.querySelector(
        '.incidencias-list-panel .panel-action'
    );


/* =========================================================
   02. COMPROBACIÓN
========================================================= */

if (!lista) {

    console.error(
        'No se encontró .incidencias-list'
    );

    return;

}


if (incidencias.length === 0) {

    console.warn(
        'No se encontraron incidencias'
    );

}


/* =========================================================
   03. ELEMENTOS DE RESUMEN
   Localizamos las tarjetas superiores.
========================================================= */

const tarjetasResumen =
    document.querySelectorAll(
        '.dashboard-cards .dashboard-card'
    );


/*
 * Referencias:
 *
 * 0 -> Total
 * 1 -> Pendientes
 * 2 -> En curso
 * 3 -> Urgentes
 */

const tarjetaTotal =
    tarjetasResumen[0] || null;

const tarjetaPendientes =
    tarjetasResumen[1] || null;

const tarjetaEnCurso =
    tarjetasResumen[2] || null;

const tarjetaUrgentes =
    tarjetasResumen[3] || null;


/* =========================================================
   04. NORMALIZACIÓN DE TEXTO
   Permite comparar ignorando mayúsculas y acentos.
========================================================= */

function normalizar(texto) {

    return String(texto)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

}


/* =========================================================
   05. OBTENER INFORMACIÓN DE UNA INCIDENCIA
========================================================= */

function obtenerTitulo(incidencia) {

    const elemento =
        incidencia.querySelector(
            '.incidencia-title strong'
        );

    return elemento
        ? elemento.textContent.trim()
        : '';

}


function obtenerId(incidencia) {

    const elemento =
        incidencia.querySelector(
            '.incidencia-id'
        );

    return elemento
        ? elemento.textContent.trim()
        : '';

}


function obtenerDescripcion(incidencia) {

    const elemento =
        incidencia.querySelector(
            '.incidencia-main > p'
        );

    return elemento
        ? elemento.textContent.trim()
        : '';

}


function obtenerResponsable(incidencia) {

    const elementos =
        incidencia.querySelectorAll(
            '.incidencia-meta span'
        );

    /*
     * La estructura actual es:
     *
     * 0 -> Cliente
     * 1 -> Responsable
     * 2 -> Fecha
     */

    return elementos[1]
        ? elementos[1].textContent.trim()
        : '';

}


function obtenerEstado(incidencia) {

    const elemento =
        incidencia.querySelector(
            '.status-badge'
        );

    return elemento
        ? elemento.textContent.trim()
        : '';

}


function obtenerPrioridad(incidencia) {

    const elemento =
        incidencia.querySelector(
            '.priority-badge'
        );

    return elemento
        ? elemento.textContent.trim()
        : '';

}


function obtenerCliente(incidencia) {

    const elementos =
        incidencia.querySelectorAll(
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


/* =========================================================
   06. OBTENER INCIDENCIAS FILTRADAS
========================================================= */

// Mismo criterio que los filtros de Usuarios: dentro de un
// filtro vale CUALQUIERA de las opciones marcadas (sin
// ninguna marcada, no filtra); entre filtros, deben
// cumplirse todos.
function coincideSeleccion(filtro, valor) {

    if (!filtro) {
        return true;
    }

    const seleccionados = window.obtenerSeleccionMultiFiltro(filtro).map(normalizar);

    return seleccionados.length === 0 || seleccionados.includes(normalizar(valor));

}

function obtenerIncidenciasFiltradas() {

    const texto = buscarIncidencia ? normalizar(buscarIncidencia.value) : '';

    return incidencias.filter(incidencia => {

        // Búsqueda general (título, cliente, descripción o nº).
        if (texto !== '') {

            const coincideTexto = [
                obtenerTitulo(incidencia),
                obtenerCliente(incidencia),
                obtenerDescripcion(incidencia),
                obtenerId(incidencia)
            ].some(valor => normalizar(valor).includes(texto));

            if (!coincideTexto) {
                return false;
            }

        }

        return coincideSeleccion(filtroEstado, obtenerEstado(incidencia))
            && coincideSeleccion(filtroPrioridad, obtenerPrioridad(incidencia))
            && coincideSeleccion(filtroResponsable, obtenerResponsable(incidencia));

    });

}



/* =========================================================
   07. MOSTRAR / OCULTAR INCIDENCIAS
========================================================= */

function mostrarIncidencias() {

    const filtradas =
        obtenerIncidenciasFiltradas();

    const totalPaginas =
        obtenerTotalPaginas(filtradas);

    paginaActual = Math.min(
        paginaActual,
        totalPaginas
    );

    const inicio =
        incidenciasPorPagina === Infinity
            ? 0
            : (paginaActual - 1) * incidenciasPorPagina;

    const fin =
        incidenciasPorPagina === Infinity
            ? filtradas.length
            : inicio + incidenciasPorPagina;

    const incidenciasPagina =
        filtradas.slice(inicio, fin);


    /*
     * Primero ocultamos todas.
     */

    incidencias.forEach(
        incidencia => {

            incidencia.style.display =
                incidenciasPagina.includes(incidencia)
                    ? ''
                    : 'none';

        }
    );


    /*
     * Actualizamos los diferentes
     * elementos de resumen.
     */

    actualizarResumen(filtradas);

    actualizarContadores(filtradas);

    actualizarPaginacion(totalPaginas);

}


function actualizarContadores(filtradas) {

    if (!mostrando) {
        return;
    }

    if (filtradas.length === 0) {
        mostrando.textContent =
            'Mostrando 0 de 0 incidencias';
        return;
    }

    if (incidenciasPorPagina === Infinity) {
        mostrando.textContent =
            `Mostrando ${filtradas.length} de ${filtradas.length} incidencias`;
        return;
    }

    const inicio =
        (paginaActual - 1) * incidenciasPorPagina + 1;

    const fin =
        Math.min(
            inicio + incidenciasPorPagina - 1,
            filtradas.length
        );

    mostrando.textContent =
        `Mostrando ${inicio}-${fin} de ${filtradas.length} incidencias`;

}


function obtenerTotalPaginas(filtradas) {

    if (filtradas.length === 0) {
        return 1;
    }

    return incidenciasPorPagina === Infinity
        ? 1
        : Math.ceil(
            filtradas.length / incidenciasPorPagina
        );

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
            boton.style.display =
                incidenciasPorPagina === Infinity ||
                numeroPagina > totalPaginas
                    ? 'none'
                    : 'inline-flex';

            boton.classList.toggle(
                'active',
                numeroPagina === paginaActual
            );
        }

    });

    const puntos =
        document.querySelector('.pagination-dots');

    if (puntos) {
        puntos.style.display =
            totalPaginas > 5 &&
            incidenciasPorPagina !== Infinity
                ? 'inline-flex'
                : 'none';
    }

}


/* =========================================================
   08. ACTUALIZAR TARJETAS SUPERIORES
========================================================= */

function actualizarResumen(filtradas) {

    const total =
        filtradas.length;


    const pendientes =
        filtradas.filter(
            incidencia =>
                normalizar(
                    obtenerEstado(incidencia)
                ) === 'pendiente'
        ).length;


    const enCurso =
        filtradas.filter(
            incidencia =>
                normalizar(
                    obtenerEstado(incidencia)
                ) === 'en curso'
        ).length;


    const urgentes =
        filtradas.filter(
            incidencia =>
                normalizar(
                    obtenerPrioridad(incidencia)
                ) === 'alta'
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
        tarjetaUrgentes,
        urgentes
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
   09. BOTÓN BUSCAR
========================================================= */

if (btnBuscar) {

    btnBuscar.addEventListener(
        'click',
        () => {

            paginaActual = 1;

            mostrarIncidencias();

        }
    );

}


/* =========================================================
   12. BÚSQUEDA EN TIEMPO REAL
   Mientras se escribe también se actualiza el listado.
========================================================= */

// Recordar búsqueda y filtros al recargar la página (mismo
// comportamiento que Usuarios; ver crearMemoriaFiltros() en
// js/multi-select-filter.js).
const filtrosSeleccion = [
    filtroEstado,
    filtroPrioridad,
    filtroResponsable
].filter(Boolean);

const memoriaFiltros = window.crearMemoriaFiltros(
    'filtrosIncidencias',
    [buscarIncidencia, ...filtrosSeleccion].filter(Boolean)
);

if (buscarIncidencia) {

    buscarIncidencia.addEventListener(
        'input',
        () => {

            memoriaFiltros.guardar();

            paginaActual = 1;

            mostrarIncidencias();

        }
    );

}


/* =========================================================
   13. CAMBIO DE FILTROS
========================================================= */

filtrosSeleccion.forEach(filtro => {

    filtro.addEventListener('change', () => {

        memoriaFiltros.guardar();

        paginaActual = 1;

        mostrarIncidencias();

    });

});

// Vacía búsqueda y filtros (botones Limpiar y "Ver todas").
function limpiarFiltrosIncidencias() {

    if (buscarIncidencia) {
        buscarIncidencia.value = '';
    }

    filtrosSeleccion.forEach(filtro => window.limpiarMultiFiltro(filtro));

    memoriaFiltros.olvidar();

}


/* =========================================================
   14. PAGINACIÓN
========================================================= */

botonesPaginacion.forEach(boton => {

    boton.addEventListener('click', () => {

        const accion = boton.dataset.page;

        if (accion === 'prev' && paginaActual > 1) {
            paginaActual--;
        }

        if (accion === 'next') {
            const totalPaginas = obtenerTotalPaginas(
                obtenerIncidenciasFiltradas()
            );

            if (paginaActual < totalPaginas) {
                paginaActual++;
            }
        }

        const numeroPagina = Number(accion);

        if (!Number.isNaN(numeroPagina)) {
            paginaActual = numeroPagina;
        }

        mostrarIncidencias();

    });

});


if (selectorPorPagina) {

    selectorPorPagina.addEventListener('change', () => {

        incidenciasPorPagina =
            selectorPorPagina.value === 'all'
                ? Infinity
                : Number(selectorPorPagina.value);

        paginaActual = 1;

        mostrarIncidencias();

    });

}
/* =========================================================
    15. LIMPIAR FILTROS
========================================================= */

if (btnLimpiar) {

    btnLimpiar.addEventListener(
        'click',
        () => {

            limpiarFiltrosIncidencias();

            paginaActual = 1;


            mostrarIncidencias();

        }
    );

}


/* =========================================================
    16. BOTÓN "VER TODAS"
   Elimina todos los filtros.
========================================================= */

if (btnVerTodas) {

    btnVerTodas.addEventListener(
        'click',
        () => {

            limpiarFiltrosIncidencias();

            paginaActual = 1;


            mostrarIncidencias();

        }
    );

}


/* =========================================================
   16. CLIC EN UNA INCIDENCIA
   Preparado para abrir posteriormente
   la ficha/detalle de la incidencia.
========================================================= */

incidencias.forEach(
    incidencia => {

        incidencia.style.cursor =
            'pointer';


        incidencia.addEventListener(
            'click',
            evento => {

                /*
                 * Si en el futuro añadimos botones
                 * dentro de la incidencia, evitamos
                 * que estos clics disparen el detalle.
                 */

                if (
                    evento.target.closest(
                        'button, a, input, select'
                    )
                ) {

                    return;

                }


                const id =
                    obtenerId(incidencia);


                console.log(
                    'Incidencia seleccionada:',
                    id
                );


                /*
                 * Aquí podremos posteriormente
                 * hacer algo como:
                 *
                 * window.location.href =
                 *     'incidencia.php?id=1048';
                 */

            }
        );

    }
);


/* =========================================================
   17. BOTÓN "+ NUEVA INCIDENCIA"
   Actualmente solo dejamos preparada
   la funcionalidad.
========================================================= */

const btnNuevaIncidencia =
    document.querySelector(
        '.page-header-actions .config-save-button'
    );


if (btnNuevaIncidencia) {

    btnNuevaIncidencia.addEventListener(
        'click',
        () => {

            /*
             * Cuando creemos el formulario:
             *
             * window.location.href =
             *     'nueva-incidencia.php';
             */

            console.log(
                'Nueva incidencia'
            );

        }
    );

}


/* =========================================================
   18. INICIALIZACIÓN
========================================================= */

memoriaFiltros.restaurar();
mostrarIncidencias();


});

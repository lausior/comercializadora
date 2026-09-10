document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       00. VALIDACIÓN DE FORMULARIOS DE USUARIO
       (crear_usuario.php / editar_usuario.php)
       Se coloca ANTES del "return" del punto 02 porque esas
       páginas no tienen tabla de usuarios y el script cortaría
       aquí su ejecución si se pusiera más abajo.
    ========================================================= */

    function validarNombreApellidos(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (texto.length < 2) {
            return 'Debe tener al menos 2 caracteres.';
        }

        if (!/^[A-Za-zÀ-ÖØ-öø-ÿ'\- ]+$/.test(texto)) {
            return 'Solo se permiten letras, espacios, guiones y apóstrofos.';
        }

        return null;

    }

    function validarUsername(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (texto.length < 2) {
            return 'Debe tener al menos 2 caracteres.';
        }

        if (!/^[A-Za-z0-9._]+$/.test(texto)) {
            return 'Solo letras sin acentos, números, puntos y guiones bajos (sin espacios).';
        }

        return null;

    }

    function validarEmail(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(texto)) {
            return 'Introduce un email con un formato válido.';
        }

        return null;

    }

    function validarTelefono(valor) {

        const texto = valor.trim();

        // El teléfono es opcional
        if (texto === '') {
            return null;
        }

        if (!/^\+?[0-9\s\-()]+$/.test(texto)) {
            return 'Solo números, espacios, guiones, paréntesis y un "+" inicial.';
        }

        const digitos = texto.replace(/\D/g, '');

        if (digitos.length < 7 || digitos.length > 15) {
            return 'Introduce un teléfono válido (nacional o internacional).';
        }

        return null;

    }

    function validarSeleccionRequerida(valor) {

        if (!valor || valor.trim() === '') {
            return 'Debes seleccionar una opción.';
        }

        return null;

    }

    const VALIDADORES_USUARIO = {
        nombre: validarNombreApellidos,
        apellidos: validarNombreApellidos,
        username: validarUsername,
        email: validarEmail,
        telefono: validarTelefono,
        id_empresa: validarSeleccionRequerida,
        id_rol: validarSeleccionRequerida
    };

    function validarCampoUsuario(input) {

        const validador = VALIDADORES_USUARIO[input.name];

        if (!validador) {
            return true;
        }

        const error = validador(input.value);
        const contenedorError = document.getElementById('error-' + input.name);

        if (error) {

            input.classList.add('input-error');

            if (contenedorError) {
                contenedorError.textContent = error;
            }

            return false;

        }

        input.classList.remove('input-error');

        if (contenedorError) {
            contenedorError.textContent = '';
        }

        return true;

    }

    function mostrarMensajeGeneral(formulario, mensaje) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = mensaje;
        contenedor.style.display = 'block';

    }

    function ocultarMensajeGeneral(formulario) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = '';
        contenedor.style.display = 'none';

    }

    function quedanCamposInvalidos(formulario) {

        return Object.keys(VALIDADORES_USUARIO).some(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return false;
            }

            return VALIDADORES_USUARIO[nombreCampo](input.value) !== null;

        });

    }

    function inicializarValidacionFormularioUsuario() {

        const formulario = document.querySelector('.config-card form');

        if (!formulario) {
            return;
        }

        Object.keys(VALIDADORES_USUARIO).forEach(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return;
            }

            // Marca el error justo al desenfocar el campo
            input.addEventListener('blur', () => {
                validarCampoUsuario(input);
            });

            // Para los <select> (empresa, rol), el evento relevante
            // es "change", no "blur"
            input.addEventListener('change', () => {
                validarCampoUsuario(input);
            });

            // Si mientras escribe el campo pasa a ser válido,
            // quita el error inmediatamente
            input.addEventListener('input', () => {

                const validador = VALIDADORES_USUARIO[input.name];
                const error = validador(input.value);

                if (!error) {
                    input.classList.remove('input-error');
                    const contenedorError = document.getElementById('error-' + input.name);
                    if (contenedorError) {
                        contenedorError.textContent = '';
                    }
                }

                // Si ya no queda ningún campo inválido, se
                // oculta también el mensaje general
                if (!quedanCamposInvalidos(formulario)) {
                    ocultarMensajeGeneral(formulario);
                }

            });

        });

        formulario.addEventListener('submit', (event) => {

            let formularioValido = true;

            Object.keys(VALIDADORES_USUARIO).forEach(nombreCampo => {

                const input = formulario.querySelector('#' + nombreCampo);

                if (!input) {
                    return;
                }

                const campoValido = validarCampoUsuario(input);

                if (!campoValido) {
                    formularioValido = false;
                }

            });

            if (!formularioValido) {

                event.preventDefault();

                mostrarMensajeGeneral(
                    formulario,
                    'Hay campos obligatorios sin completar o con un formato incorrecto. Revisa los campos marcados en rojo.'
                );

                const mensajeGeneral = formulario.querySelector('#form-error-general');

                if (mensajeGeneral) {
                    mensajeGeneral.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

            } else {

                ocultarMensajeGeneral(formulario);

            }

        });

    }

    inicializarValidacionFormularioUsuario();


    /* =========================================================
       01. ELEMENTOS DEL DOM
    ========================================================= */

    const tbody = document.getElementById('usuariosBody');
    const tabla = document.querySelector('.usuarios-table');

    const contador = document.getElementById('usuariosContador');
    const mostrando = document.getElementById('usuariosMostrando');

    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    const selectorPorPagina =
        document.getElementById('selectorPorPagina');

    const filtros =
        document.querySelectorAll('.column-filter');

    const botonesOrden =
        document.querySelectorAll(
            '.usuarios-table .sort-button'
        );

    const botonesPaginacion =
        document.querySelectorAll(
            '.usuarios-pagination .pagination-button'
        );


    /* =========================================================
       02. COMPROBACIÓN DE ELEMENTOS
    ========================================================= */

    if (!tbody || !tabla) {

        console.error(
            'No se encontró #usuariosBody o .usuarios-table'
        );

        return;

    }


    /* =========================================================
       03. CONFIGURACIÓN
    ========================================================= */

    let filas = Array.from(
        tbody.querySelectorAll('tr')
    );

    let USUARIOS_POR_PAGINA = 5;

    let paginaActual = 1;

    const USUARIOS_TOTALES = filas.length;


    /* =========================================================
       04. ORDEN ORIGINAL
    ========================================================= */

    filas.forEach((fila, index) => {

        fila.dataset.originalOrder = index;

    });


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
       06. OBTENER TEXTO DE UNA CELDA
    ========================================================= */

    function obtenerTextoCelda(
        fila,
        columna
    ) {

        const celdas =
            fila.querySelectorAll('td');

        if (!celdas[columna]) {

            return '';

        }

        return normalizar(
            celdas[columna].textContent
        );

    }


    /* =========================================================
       07. FILTRADO
    ========================================================= */

    function obtenerFilasFiltradas() {

        return filas.filter(fila => {

            let coincide = true;

            filtros.forEach(filtro => {

                const columna =
                    Number(
                        filtro.dataset.column
                    );

                const valorFiltro =
                    normalizar(
                        filtro.value
                    );

                if (valorFiltro === '') {

                    return;

                }

                const valorCelda =
                    obtenerTextoCelda(
                        fila,
                        columna
                    );

                if (
                    !valorCelda.includes(
                        valorFiltro
                    )
                ) {

                    coincide = false;

                }

            });

            return coincide;

        });

    }


    /* =========================================================
       08. TOTAL DE PÁGINAS
    ========================================================= */

    function obtenerTotalPaginas(
        filasFiltradas
    ) {

        if (
            filasFiltradas.length === 0
        ) {

            return 1;

        }

        if (
            USUARIOS_POR_PAGINA === Infinity
        ) {

            return 1;

        }

        return Math.ceil(
            filasFiltradas.length /
            USUARIOS_POR_PAGINA
        );

    }


    /* =========================================================
       09. MOSTRAR PÁGINA
    ========================================================= */

    function mostrarPagina() {

        const filasFiltradas =
            obtenerFilasFiltradas();

        const totalPaginas =
            obtenerTotalPaginas(
                filasFiltradas
            );


        /* -----------------------------------------
           CONTROLAR PÁGINA ACTUAL
        ----------------------------------------- */

        if (
            paginaActual >
            totalPaginas
        ) {

            paginaActual =
                totalPaginas;

        }

        if (
            paginaActual < 1
        ) {

            paginaActual = 1;

        }


        /* -----------------------------------------
           OCULTAR TODAS LAS FILAS
        ----------------------------------------- */

        filas.forEach(fila => {

            fila.style.display = 'none';

        });


        /* -----------------------------------------
           CALCULAR RANGO
        ----------------------------------------- */

        const inicio =
            USUARIOS_POR_PAGINA === Infinity
                ? 0
                : (
                    paginaActual - 1
                ) *
                USUARIOS_POR_PAGINA;


        const fin =
            USUARIOS_POR_PAGINA === Infinity
                ? filasFiltradas.length
                : inicio +
                USUARIOS_POR_PAGINA;


        const filasPagina =
            filasFiltradas.slice(
                inicio,
                fin
            );


        /* -----------------------------------------
           MOSTRAR FILAS
        ----------------------------------------- */

        filasPagina.forEach(fila => {

            fila.style.display = '';

        });


        /* -----------------------------------------
           ACTUALIZAR INFORMACIÓN
        ----------------------------------------- */

        actualizarContadores(
            filasFiltradas
        );

        actualizarPaginacion(
            totalPaginas
        );

    }


    /* =========================================================
       10. CONTADORES
    ========================================================= */

    function actualizarContadores(
        filasFiltradas
    ) {

        const cantidadFiltrada =
            filasFiltradas.length;


        /* -----------------------------------------
           CONTADOR SUPERIOR
        ----------------------------------------- */

        if (contador) {

            contador.textContent =
                cantidadFiltrada === 1
                    ? '1 usuario encontrado'
                    : `${cantidadFiltrada} usuarios encontrados`;

        }


        /* -----------------------------------------
           CONTADOR INFERIOR
        ----------------------------------------- */

        if (mostrando) {

            if (
                cantidadFiltrada === 0
            ) {

                mostrando.textContent =
                    `Mostrando 0 de ${USUARIOS_TOTALES} usuarios`;

                return;

            }


            if (
                USUARIOS_POR_PAGINA === Infinity
            ) {

                mostrando.textContent =
                    `Mostrando ${cantidadFiltrada} de ${cantidadFiltrada} usuarios`;

                return;

            }


            const inicio =
                (
                    paginaActual - 1
                ) *
                USUARIOS_POR_PAGINA +
                1;


            const fin =
                Math.min(
                    inicio +
                    USUARIOS_POR_PAGINA -
                    1,
                    cantidadFiltrada
                );


            mostrando.textContent =
                `Mostrando ${inicio}-${fin} de ${cantidadFiltrada} usuarios`;

        }

    }


    /* =========================================================
       11. PAGINACIÓN
    ========================================================= */

    function actualizarPaginacion(
        totalPaginas
    ) {

        botonesPaginacion.forEach(
            boton => {

                const accion =
                    boton.dataset.page;


                /* -----------------------------------------
                   ANTERIOR
                ----------------------------------------- */

                if (
                    accion === 'prev'
                ) {

                    const deshabilitado =
                        paginaActual <= 1;

                    boton.disabled =
                        deshabilitado;

                    boton.classList.toggle(
                        'disabled',
                        deshabilitado
                    );

                    return;

                }


                /* -----------------------------------------
                   SIGUIENTE
                ----------------------------------------- */

                if (
                    accion === 'next'
                ) {

                    const deshabilitado =
                        paginaActual >=
                        totalPaginas;

                    boton.disabled =
                        deshabilitado;

                    boton.classList.toggle(
                        'disabled',
                        deshabilitado
                    );

                    return;

                }


                /* -----------------------------------------
                   BOTONES NUMÉRICOS
                ----------------------------------------- */

                const numeroPagina =
                    Number(accion);

                if (
                    !Number.isNaN(
                        numeroPagina
                    )
                ) {

                    boton.style.display =
                        (
                            USUARIOS_POR_PAGINA === Infinity ||
                            numeroPagina >
                            totalPaginas
                        )
                            ? 'none'
                            : 'inline-flex';


                    boton.classList.toggle(
                        'active',
                        numeroPagina ===
                        paginaActual
                    );

                }

            }
        );

    }


    /* =========================================================
       12. EVENTOS DE PAGINACIÓN
    ========================================================= */

    botonesPaginacion.forEach(
        boton => {

            boton.addEventListener(
                'click',
                () => {

                    const accion =
                        boton.dataset.page;


                    /* -----------------------------------------
                       ANTERIOR
                    ----------------------------------------- */

                    if (
                        accion === 'prev'
                    ) {

                        if (
                            paginaActual > 1
                        ) {

                            paginaActual--;

                            mostrarPagina();

                        }

                        return;

                    }


                    /* -----------------------------------------
                       SIGUIENTE
                    ----------------------------------------- */

                    if (
                        accion === 'next'
                    ) {

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


                    /* -----------------------------------------
                       PÁGINA NUMÉRICA
                    ----------------------------------------- */

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
       13. FILTROS
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
       14. LIMPIAR FILTROS
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
       15. USUARIOS POR PÁGINA
    ========================================================= */

    if (
        selectorPorPagina
    ) {

        selectorPorPagina.addEventListener(
            'change',
            () => {

                const valor =
                    selectorPorPagina.value;


                USUARIOS_POR_PAGINA =
                    valor === 'todos'
                        ? Infinity
                        : Number(valor);


                paginaActual = 1;

                mostrarPagina();

            }
        );

    }


    /* =========================================================
       16. ORDENACIÓN
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


                    /* -----------------------------------------
                       CAMBIAR DIRECCIÓN
                    ----------------------------------------- */

                    if (
                        direccion === 'none'
                    ) {

                        direccion = 'asc';

                    } else if (
                        direccion === 'asc'
                    ) {

                        direccion = 'desc';

                    } else {

                        direccion = 'none';

                    }


                    /* -----------------------------------------
                       REINICIAR OTROS BOTONES
                    ----------------------------------------- */

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


                    /* -----------------------------------------
                       ORDEN ORIGINAL
                    ----------------------------------------- */

                    if (
                        direccion === 'none'
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


                    /* -----------------------------------------
                       ORDEN ASC/DESC
                    ----------------------------------------- */

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
                                    valorA < valorB
                                ) {

                                    return direccion === 'asc'
                                        ? -1
                                        : 1;

                                }


                                if (
                                    valorA > valorB
                                ) {

                                    return direccion === 'asc'
                                        ? 1
                                        : -1;

                                }


                                return 0;

                            }
                        );

                    }


                    /* -----------------------------------------
                       REINSERTAR FILAS
                    ----------------------------------------- */

                    filas.forEach(
                        fila => {

                            tbody.appendChild(
                                fila
                            );

                        }
                    );


                    paginaActual = 1;

                    mostrarPagina();

                }
            );

        }
    );


    /* =========================================================
       17. MODAL DE ELIMINACIÓN
    ========================================================= */

    let usuarioEliminarId = 0;


    /* -----------------------------------------
       ELEMENTOS DEL MODAL
    ----------------------------------------- */

    const modalEliminar =
        document.getElementById(
            'modalEliminar'
        );

    const nombreUsuarioEliminar =
        document.getElementById(
            'nombreUsuarioEliminar'
        );


    /* =========================================================
       18. ABRIR MODAL
    ========================================================= */

    window.abrirModalEliminar =
        function (
            id,
            nombre
        ) {

            usuarioEliminarId =
                Number(id);


            if (
                nombreUsuarioEliminar
            ) {

                nombreUsuarioEliminar.textContent =
                    nombre;

            }


            if (
                modalEliminar
            ) {

                modalEliminar.style.display =
                    'flex';

                document.body.classList.add(
                    'modal-abierto'
                );

            }

        };


    /* =========================================================
       19. CERRAR MODAL
    ========================================================= */

    window.cerrarModalEliminar =
        function () {

            usuarioEliminarId = 0;


            if (
                modalEliminar
            ) {

                modalEliminar.style.display =
                    'none';

                document.body.classList.remove(
                    'modal-abierto'
                );

            }

        };


    /* =========================================================
       20. CONFIRMAR ELIMINACIÓN
    ========================================================= */

    window.confirmarEliminarUsuario =
        function () {

            if (
                usuarioEliminarId <= 0
            ) {

                return;

            }


            window.location.href =
                'eliminar_usuario.php?id=' +
                encodeURIComponent(
                    usuarioEliminarId
                );

        };


    /* =========================================================
       21. CERRAR MODAL AL PULSAR FUERA
    ========================================================= */

    if (
        modalEliminar
    ) {

        modalEliminar.addEventListener(
            'click',
            event => {

                if (
                    event.target ===
                    modalEliminar
                ) {

                    window.cerrarModalEliminar();

                }

            }
        );

    }


    /* =========================================================
       22. CERRAR MODAL CON ESC
    ========================================================= */

    document.addEventListener(
        'keydown',
        event => {

            if (
                event.key === 'Escape' &&
                usuarioEliminarId > 0
            ) {

                window.cerrarModalEliminar();

            }

        }
    );


    /* =========================================================
       23. PAGINACIÓN INICIAL
    ========================================================= */

    mostrarPagina();

});
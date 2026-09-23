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

        if (/^[-']/.test(texto)) {
            return 'Debe empezar con una letra.';
        }

        if (!/^[A-Za-zÀ-ÖØ-öø-ÿ](?:[A-Za-zÀ-ÖØ-öø-ÿ'\- ]*[A-Za-zÀ-ÖØ-öø-ÿ])?$/.test(texto)) {
            return 'Solo se permiten letras, espacios, guiones y apóstrofos.';
        }

        if (texto.length < 2) {
            return 'Debe tener al menos 2 caracteres.';
        }

        return null;

    }

    function validarUsername(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (!/^[a-zñ]+$/.test(texto)) {
            return 'Solo se permiten letras minúsculas (incluida la ñ), sin números, espacios ni otros caracteres especiales.';
        }

        if (texto.length < 2) {
            return 'Debe tener al menos 2 caracteres.';
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

    function validarMotivoInactivo(valor) {

        const estado = document.getElementById('estado');

        if (estado && estado.value === 'Inactivo' && (!valor || valor.trim() === '')) {
            return 'Debes seleccionar un motivo.';
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
        id_rol: validarSeleccionRequerida,
        estado: validarSeleccionRequerida,
        motivo_inactivo: validarMotivoInactivo
    };

    function validarCampoUsuario(input) {

        // Campos de solo lectura (nombre, email, teléfono de la
        // cuenta EMPRESA) u ocultos (apellidos, en esa misma
        // cuenta): no se pueden editar aquí, así que tampoco se
        // validan — su valor ya viene válido de la base de datos.
        if (input.readOnly || input.closest('.form-group')?.classList.contains('hidden')) {
            return true;
        }

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

            if (input.readOnly || input.closest('.form-group')?.classList.contains('hidden')) {
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

                // Evita el doble envío (doble clic, o un segundo
                // clic porque la página tarda un instante en
                // navegar): sin esto, dos peticiones casi
                // simultáneas pueden colarse las dos antes de que
                // ninguna haya guardado nada todavía, la primera
                // crea el usuario y la segunda, al encontrarlo ya
                // creado, responde con "el username/email ya
                // existe" — un error confuso, porque el usuario SÍ
                // se ha guardado (por la primera).
                const botonGuardar = formulario.querySelector('button[type="submit"]');

                if (botonGuardar) {
                    botonGuardar.disabled = true;
                }

            }

        });

    }

    inicializarValidacionFormularioUsuario();


    /* =========================================================
       00B. MOTIVO DE INACTIVO
       El textarea solo se muestra (y solo hace falta
       rellenarlo) cuando el estado elegido es "Inactivo".
    ========================================================= */

    function actualizarVisibilidadMotivoInactivo() {

        const estado = document.getElementById('estado');
        const grupoMotivo = document.getElementById('grupo_motivo_inactivo');

        if (!estado || !grupoMotivo) {
            return;
        }

        grupoMotivo.classList.toggle('hidden', estado.value !== 'Inactivo');

    }

    const estadoUsuarioSelect = document.getElementById('estado');

    if (estadoUsuarioSelect) {

        actualizarVisibilidadMotivoInactivo();

        estadoUsuarioSelect.addEventListener('change', () => {

            actualizarVisibilidadMotivoInactivo();

            const grupoMotivo = document.getElementById('grupo_motivo_inactivo');
            const motivo = document.getElementById('motivo_inactivo');

            if (grupoMotivo && grupoMotivo.classList.contains('hidden') && motivo) {
                motivo.classList.remove('input-error');
                const contenedorError = document.getElementById('error-motivo_inactivo');
                if (contenedorError) {
                    contenedorError.textContent = '';
                }
            }

        });

    }


    /* =========================================================
       00B-2. OPCIONES DE MOTIVO SEGÚN EL ROL ELEGIDO
       (crear_usuario.php, solo cuando Rol es un <select>: SRG)
       =========================================================
       Las opciones del motivo dependen del rol: EMPRESA usa
       impago/fin_contrato, el resto usa las de USUARIO (mismas
       listas que en abrirModalDesactivarUsuario(), más abajo).
    ========================================================= */

    function actualizarOpcionesMotivoSegunRol() {

        const rolSelect = document.getElementById('id_rol');
        const motivo = document.getElementById('motivo_inactivo');

        if (!motivo || !rolSelect || rolSelect.tagName !== 'SELECT') {
            return;
        }

        const opcionRol = rolSelect.selectedOptions[0];
        const nombreRol = opcionRol ? opcionRol.dataset.rol : '';

        const opciones = nombreRol === 'EMPRESA'
            ? [['impago', 'Impago'], ['fin_contrato', 'Fin de contrato']]
            : [
                ['vacaciones', 'Vacaciones'],
                ['baja_laboral', 'Baja laboral'],
                ['baja_empresa', 'Baja en la empresa']
            ];

        const valorPrevio = motivo.value;

        motivo.innerHTML = '<option value="">Selecciona un motivo</option>';

        opciones.forEach(([valor, etiqueta]) => {

            const opcion = document.createElement('option');
            opcion.value = valor;
            opcion.textContent = etiqueta;

            motivo.appendChild(opcion);

        });

        // Si la opción que tenía elegida sigue existiendo en la
        // nueva lista, se conserva; si no (venía de otro rol),
        // se pierde y hay que volver a elegir.
        motivo.value = valorPrevio;

    }

    const rolSelectParaMotivo = document.getElementById('id_rol');

    if (rolSelectParaMotivo && rolSelectParaMotivo.tagName === 'SELECT') {

        actualizarOpcionesMotivoSegunRol();

        rolSelectParaMotivo.addEventListener('change', actualizarOpcionesMotivoSegunRol);

    }


    /* =========================================================
       00C. BLOQUEAR ROL A "USUARIO" SEGÚN LA EMPRESA ELEGIDA
       (crear_usuario.php, solo para SRG/NG)
       =========================================================
       Una empresa solo puede tener un usuario con rol EMPRESA
       (su "admin"). Si la empresa elegida ya tiene uno, el
       desplegable Rol pasa a mostrar únicamente la opción
       "Usuario" (las demás se ocultan, no solo se deshabilitan,
       para no dejar un desplegable lleno de opciones en gris) y
       se fija ese valor. Sin mensaje informativo aparte, para no
       romper el diseño del formulario. window.EMPRESAS_CON_ROL_EMPRESA
       lo rellena crear_usuario.php solo cuando aplica.
    ========================================================= */

    function bloquearRolSegunEmpresa() {

        const empresaSelect = document.getElementById('id_empresa');
        const rolSelect = document.getElementById('id_rol');

        if (
            !empresaSelect ||
            !rolSelect ||
            empresaSelect.tagName !== 'SELECT' ||
            rolSelect.tagName !== 'SELECT' ||
            !Array.isArray(window.EMPRESAS_CON_ROL_EMPRESA)
        ) {
            return;
        }

        const opciones = Array.from(rolSelect.options);
        const opcionUsuario = opciones.find(opcion => opcion.dataset.rol === 'USUARIO');

        function actualizar() {

            const idEmpresa = Number(empresaSelect.value);

            const empresaYaTieneAdmin =
                idEmpresa > 0 &&
                window.EMPRESAS_CON_ROL_EMPRESA.includes(idEmpresa);

            opciones.forEach(opcion => {
                const ocultar = empresaYaTieneAdmin && opcion !== opcionUsuario;
                opcion.hidden = ocultar;
                opcion.disabled = ocultar;
            });

            if (empresaYaTieneAdmin && opcionUsuario) {
                rolSelect.value = opcionUsuario.value;
            }

        }

        empresaSelect.addEventListener('change', actualizar);

        actualizar();

    }

    bloquearRolSegunEmpresa();


    /* =========================================================
       01. ELEMENTOS DEL DOM
    ========================================================= */

    const tbody = document.getElementById('usuariosBody');
    const tabla = document.querySelector('.usuarios-table');

    const contador = document.getElementById('usuariosContador');
    const mostrando = document.getElementById('usuariosMostrando');

    const selectorPorPagina =
        document.getElementById('selectorPorPagina');

    // Los filtros de escritorio (fila de la tabla) y los del
    // panel móvil comparten clase y data-column, así que un
    // único selector los recoge a todos.
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

    let USUARIOS_TOTALES = filas.length;

    // Mismo punto de corte que el @media (max-width: 680px)
    // del CSS que decide entre vista de escritorio y móvil.
    const MOBILE_BREAKPOINT = 680;

    function esMovil() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }


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
            .replace(/[̀-ͯ]/g, '')
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
       06B. VALOR "LIMPIO" DE UNA COLUMNA PARA LOS FILTROS
       DESPLEGABLES
       =========================================================
       La celda de "Usuario" (columna 0) mezcla en su texto el
       nombre completo y el @username, así que no sirve para
       comparar contra las opciones del desplegable (que son
       solo nombres completos). Usamos los data-* de la fila,
       que ya traen el valor limpio de cada campo.
    ========================================================= */

    const CAMPO_POR_COLUMNA = {
        0: 'nombre',
        1: 'email',
        2: 'telefono',
        3: 'rol',
        4: 'empresa',
        5: 'estado'
    };

    function obtenerValorFiltroFila(fila, columna) {

        const campo = CAMPO_POR_COLUMNA[columna];

        if (campo && fila.dataset[campo] !== undefined) {
            return normalizar(fila.dataset[campo]);
        }

        return obtenerTextoCelda(fila, columna);

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

                // Filtros de tipo "Rol"/"Empresa": el usuario
                // puede marcar varias opciones a la vez, y la
                // fila pasa si su valor coincide con CUALQUIERA
                // de las marcadas (si no hay ninguna marcada,
                // el filtro no se aplica).
                if (filtro.classList.contains('multi-select-filter')) {

                    const seleccionados =
                        window.obtenerSeleccionMultiFiltro(filtro)
                            .map(normalizar);

                    if (seleccionados.length === 0) {
                        return;
                    }

                    const valorFila =
                        obtenerValorFiltroFila(
                            fila,
                            columna
                        );

                    if (!seleccionados.includes(valorFila)) {
                        coincide = false;
                    }

                    return;

                }

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
       12B. RECORDAR FILTROS ENTRE RECARGAS
       =========================================================
       Activar/desactivar un usuario desde el listado navega a
       cambiar_estado_usuario.php, que vuelve a redirigir aquí:
       la página se recarga entera y, sin esto, los filtros
       marcados se perderían. Se guardan en sessionStorage (no
       localStorage) para que no sobrevivan más allá de la
       pestaña/sesión actual del navegador.
    ========================================================= */

    const CLAVE_FILTROS_GUARDADOS = 'filtrosUsuarios';

    function guardarFiltros() {

        const datos = {};

        filtros.forEach(filtro => {

            const columna = filtro.dataset.column;

            const valor = filtro.classList.contains('multi-select-filter')
                ? window.obtenerSeleccionMultiFiltro(filtro)
                : filtro.value;

            const vacio = Array.isArray(valor) ? valor.length === 0 : valor === '';

            // Los filtros de escritorio y de móvil comparten
            // data-column pero son elementos distintos; solo uno
            // de los dos tiene valor a la vez, así que el vacío
            // del otro no debe pisarlo.
            if (!vacio || datos[columna] === undefined) {
                datos[columna] = valor;
            }

        });

        try {
            sessionStorage.setItem(CLAVE_FILTROS_GUARDADOS, JSON.stringify(datos));
        } catch (error) {
            // Almacenamiento no disponible (modo privado, etc.):
            // seguimos sin recordar filtros, sin romper nada.
        }

    }

    function restaurarFiltrosGuardados() {

        let datos = null;

        try {
            datos = JSON.parse(sessionStorage.getItem(CLAVE_FILTROS_GUARDADOS));
        } catch (error) {
            datos = null;
        }

        if (!datos) {
            return;
        }

        filtros.forEach(filtro => {

            const columna = filtro.dataset.column;

            if (!(columna in datos)) {
                return;
            }

            const valor = datos[columna];

            if (filtro.classList.contains('multi-select-filter')) {
                window.marcarSeleccionMultiFiltro(filtro, valor);
            } else if (typeof valor === 'string') {
                filtro.value = valor;
            }

        });

    }

    function olvidarFiltrosGuardados() {

        try {
            sessionStorage.removeItem(CLAVE_FILTROS_GUARDADOS);
        } catch (error) {
            // Nada que limpiar si no hay almacenamiento disponible.
        }

    }


    /* =========================================================
       13. FILTROS
    ========================================================= */

    filtros.forEach(filtro => {

        filtro.addEventListener(
            'input',
            () => {

                guardarFiltros();

                paginaActual = 1;

                mostrarPagina();

            }
        );


        filtro.addEventListener(
            'change',
            () => {

                guardarFiltros();

                paginaActual = 1;

                mostrarPagina();

            }
        );

    });


    /* =========================================================
       14. LIMPIAR FILTROS
       Hay dos botones (el de la tabla de escritorio y el del
       panel móvil); los dos vacían el mismo conjunto de
       inputs, escritorio y móvil incluidos.
    ========================================================= */

    document.querySelectorAll('#btnLimpiarFiltros, #btnLimpiarFiltrosMovil').forEach(btn => {

        btn.addEventListener(
            'click',
            () => {

                filtros.forEach(
                    filtro => {

                        if (filtro.classList.contains('multi-select-filter')) {
                            window.limpiarMultiFiltro(filtro);
                        } else {
                            filtro.value = '';
                        }

                    }
                );

                olvidarFiltrosGuardados();


                paginaActual = 1;

                mostrarPagina();

            }
        );

    });


    /* =========================================================
       14B. EXPORTAR PDF
       Exporta los usuarios que cumplen los filtros activos
       (ver js/exportar-pdf.js).
    ========================================================= */

    const btnExportarPDF = document.getElementById('btnExportarPDF');

    if (btnExportarPDF) {

        btnExportarPDF.addEventListener('click', () => {
            exportarListadoPDF('exportar_pdf.php', obtenerFilasFiltradas);
        });

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
       16B. PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
    ========================================================= */

    const btnToggleFiltros = document.getElementById('btnToggleFiltros');
    const panelFiltros = document.getElementById('panelFiltrosUsuarios');

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
       16C. TARJETA DE DETALLE (SOLO MÓVIL)
       En escritorio, tocar la fila no hace nada — ahí ya se
       ve todo y están los botones Editar/Eliminar de siempre.
    ========================================================= */

    const modalDetalle = document.getElementById('modalDetalleUsuario');

    const detalleAvatar = document.getElementById('detalleUsuarioAvatar');
    const detalleNombre = document.getElementById('detalleUsuarioNombre');
    const detalleUsername = document.getElementById('detalleUsuarioUsername');
    const detalleEmail = document.getElementById('detalleUsuarioEmail');
    const detalleTelefono = document.getElementById('detalleUsuarioTelefono');
    const detalleRol = document.getElementById('detalleUsuarioRol');
    const detalleEmpresa = document.getElementById('detalleUsuarioEmpresa');
    const detalleEstado = document.getElementById('detalleUsuarioEstado');
    const detallePasswordEstado = document.getElementById('detalleUsuarioPasswordEstado');
    const detalleMotivoItem = document.getElementById('detalleUsuarioMotivoItem');
    const detalleMotivo = document.getElementById('detalleUsuarioMotivo');
    const btnDetalleEditar = document.getElementById('btnDetalleEditarUsuario');
    const btnDetalleEliminar = document.getElementById('btnDetalleEliminarUsuario');

    let usuarioDetalleActual = null;

    function abrirModalDetalleUsuario(fila) {

        usuarioDetalleActual = fila.dataset;

        if (detalleAvatar) {
            detalleAvatar.textContent = fila.dataset.iniciales || '';
        }

        if (detalleNombre) {
            detalleNombre.textContent = fila.dataset.nombre || '';
        }

        if (detalleUsername) {
            detalleUsername.textContent = fila.dataset.usuario || '';
        }

        if (detalleEmail) {
            detalleEmail.textContent = fila.dataset.email || '—';
        }

        if (detalleTelefono) {
            detalleTelefono.textContent = fila.dataset.telefono || '—';
        }

        if (detalleRol) {
            detalleRol.textContent = fila.dataset.rol || '—';
        }

        if (detalleEmpresa) {
            detalleEmpresa.textContent = fila.dataset.empresa || '—';
        }

        if (detalleEstado) {
            detalleEstado.textContent = fila.dataset.estado || '—';
            detalleEstado.classList.toggle('text-success', fila.dataset.estado === 'Activo');
            detalleEstado.classList.toggle('text-danger', fila.dataset.estado === 'Inactivo');
        }

        if (detallePasswordEstado) {
            detallePasswordEstado.textContent = fila.dataset.passwordEstado || '—';
        }

        if (detalleMotivoItem) {
            const esInactivo = fila.dataset.estado === 'Inactivo';
            detalleMotivoItem.classList.toggle('hidden', !esInactivo);

            if (detalleMotivo) {
                detalleMotivo.textContent = fila.dataset.motivo || '-';
            }
        }

        if (btnDetalleEditar) {
            btnDetalleEditar.href = 'editar_usuario.php?id=' + encodeURIComponent(fila.dataset.id);
        }

        if (modalDetalle) {
            modalDetalle.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    }

    window.cerrarModalDetalleUsuario = function () {

        usuarioDetalleActual = null;

        if (modalDetalle) {
            modalDetalle.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    filas.forEach(fila => {

        fila.addEventListener('click', () => {
            abrirModalDetalleUsuario(fila);
        });

    });

    if (btnDetalleEliminar) {

        btnDetalleEliminar.addEventListener('click', () => {

            if (!usuarioDetalleActual) {
                return;
            }

            const id = usuarioDetalleActual.id;
            const nombre = usuarioDetalleActual.nombre;
            const rol = usuarioDetalleActual.rol;

            window.cerrarModalDetalleUsuario();
            window.abrirModalEliminar(id, nombre, rol);

        });

    }

    if (modalDetalle) {

        modalDetalle.addEventListener('click', event => {

            if (event.target === modalDetalle) {
                window.cerrarModalDetalleUsuario();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && usuarioDetalleActual) {
            window.cerrarModalDetalleUsuario();
        }

    });


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

    const avisoEmpresaEliminar =
        document.getElementById(
            'avisoEmpresaEliminar'
        );


    /* =========================================================
       18. ABRIR MODAL
    ========================================================= */

    window.abrirModalEliminar =
        function (
            id,
            nombre,
            rol
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
                avisoEmpresaEliminar
            ) {

                avisoEmpresaEliminar.style.display =
                    rol === 'EMPRESA' ? 'block' : 'none';

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

    let usuarioEliminarEnCurso = false;

    window.confirmarEliminarUsuario =
        async function () {

            if (
                usuarioEliminarId <= 0 ||
                usuarioEliminarEnCurso
            ) {

                return;

            }


            const idEliminado = usuarioEliminarId;

            usuarioEliminarEnCurso = true;

            try {

                const respuesta = await fetch(
                    'eliminar_usuario.php?id=' +
                    encodeURIComponent(idEliminado) +
                    '&ajax=1',
                    { headers: { Accept: 'application/json' } }
                );

                const datos = await respuesta.json();

                if (!respuesta.ok || !datos.ok) {
                    throw new Error(datos.error || 'No se ha podido eliminar el usuario.');
                }

                window.cerrarModalEliminar();

                const fila = tbody.querySelector(
                    `tr[data-id="${idEliminado}"]`
                );

                if (fila) {
                    fila.remove();
                    filas = filas.filter(filaActual => filaActual !== fila);
                    USUARIOS_TOTALES = filas.length;
                    mostrarPagina();
                }

                mostrarNotificacionEliminacion(datos);

            } catch (error) {
                window.alert(error.message);
            } finally {
                usuarioEliminarEnCurso = false;
            }

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
       23. RESTABLECER CONTRASEÑA DESDE EL LISTADO
       Mismo patrón que el modal de eliminación: un único
       modal reutilizado por todas las filas, con el id del
       usuario objetivo guardado en una variable hasta que
       se confirma o se cancela.
    ========================================================= */

    let usuarioResetPasswordId = 0;

    const modalResetPasswordListado = document.getElementById('modalResetPasswordListado');
    const nombreUsuarioResetPassword = document.getElementById('nombreUsuarioResetPassword');

    window.abrirModalResetPasswordListado = function (id, nombre) {

        usuarioResetPasswordId = Number(id);

        if (nombreUsuarioResetPassword) {
            nombreUsuarioResetPassword.textContent = nombre;
        }

        if (modalResetPasswordListado) {
            modalResetPasswordListado.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };

    window.cerrarModalResetPasswordListado = function () {

        usuarioResetPasswordId = 0;

        if (modalResetPasswordListado) {
            modalResetPasswordListado.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    window.confirmarResetPasswordListado = function () {

        if (usuarioResetPasswordId <= 0) {
            return;
        }

        window.location.href =
            'resetear_password.php?id=' + encodeURIComponent(usuarioResetPasswordId);

    };

    if (modalResetPasswordListado) {

        modalResetPasswordListado.addEventListener('click', event => {

            if (event.target === modalResetPasswordListado) {
                window.cerrarModalResetPasswordListado();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && usuarioResetPasswordId > 0) {
            window.cerrarModalResetPasswordListado();
        }

    });


    /* =========================================================
       23B. DESACTIVAR USUARIO DESDE EL LISTADO (PIDE MOTIVO)
       Mismo patrón que el modal de restablecer contraseña,
       salvo que aquí sí hay que enviar un dato (el motivo), así
       que en vez de redirigir con window.location.href se envía
       un formulario real por POST a cambiar_estado_usuario.php.
    ========================================================= */

    let usuarioDesactivarAbierto = false;

    const modalDesactivarUsuario = document.getElementById('modalDesactivarUsuario');
    const nombreUsuarioDesactivar = document.getElementById('nombreUsuarioDesactivar');
    const idUsuarioDesactivarInput = document.getElementById('idUsuarioDesactivar');
    const motivoDesactivarUsuario = document.getElementById('motivoDesactivarUsuario');
    const formDesactivarUsuario = document.getElementById('formDesactivarUsuario');

    // Las opciones del select dependen del rol del usuario que
    // se está desactivando (mismas listas que en el select de
    // crear_usuario.php/editar_usuario.php y, para EMPRESA, que
    // el de crear_empresa.php/editar_empresa.php).
    const MOTIVOS_DESACTIVAR_USUARIO = {
        USUARIO: [
            ['vacaciones', 'Vacaciones'],
            ['baja_laboral', 'Baja laboral'],
            ['baja_empresa', 'Baja en la empresa']
        ],
        EMPRESA: [
            ['impago', 'Impago'],
            ['fin_contrato', 'Fin de contrato']
        ]
    };

    window.abrirModalDesactivarUsuario = function (id, nombre, rol) {

        usuarioDesactivarAbierto = true;

        if (idUsuarioDesactivarInput) {
            idUsuarioDesactivarInput.value = id;
        }

        if (nombreUsuarioDesactivar) {
            nombreUsuarioDesactivar.textContent = nombre;
        }

        if (motivoDesactivarUsuario) {

            const opciones = MOTIVOS_DESACTIVAR_USUARIO[rol] || MOTIVOS_DESACTIVAR_USUARIO.USUARIO;

            motivoDesactivarUsuario.innerHTML = '<option value="">Selecciona un motivo</option>';

            opciones.forEach(([valor, etiqueta]) => {

                const opcion = document.createElement('option');
                opcion.value = valor;
                opcion.textContent = etiqueta;

                motivoDesactivarUsuario.appendChild(opcion);

            });

            motivoDesactivarUsuario.classList.remove('input-error');

        }

        const contenedorError = document.getElementById('error-motivoDesactivarUsuario');
        if (contenedorError) {
            contenedorError.textContent = '';
        }

        if (modalDesactivarUsuario) {
            modalDesactivarUsuario.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };

    window.cerrarModalDesactivarUsuario = function () {

        usuarioDesactivarAbierto = false;

        if (modalDesactivarUsuario) {
            modalDesactivarUsuario.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    if (formDesactivarUsuario && motivoDesactivarUsuario) {

        formDesactivarUsuario.addEventListener('submit', event => {

            if (motivoDesactivarUsuario.value === '') {

                event.preventDefault();

                motivoDesactivarUsuario.classList.add('input-error');

                const contenedorError = document.getElementById('error-motivoDesactivarUsuario');
                if (contenedorError) {
                    contenedorError.textContent = 'Debes seleccionar un motivo.';
                }

                motivoDesactivarUsuario.focus();

            }

        });

        motivoDesactivarUsuario.addEventListener('change', () => {

            motivoDesactivarUsuario.classList.remove('input-error');

            const contenedorError = document.getElementById('error-motivoDesactivarUsuario');
            if (contenedorError) {
                contenedorError.textContent = '';
            }

        });

    }

    if (modalDesactivarUsuario) {

        modalDesactivarUsuario.addEventListener('click', event => {

            if (event.target === modalDesactivarUsuario) {
                window.cerrarModalDesactivarUsuario();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && usuarioDesactivarAbierto) {
            window.cerrarModalDesactivarUsuario();
        }

    });


    /* =========================================================
       24. PAGINACIÓN INICIAL
    ========================================================= */

    restaurarFiltrosGuardados();
    mostrarPagina();

});

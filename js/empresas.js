document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       00. VALIDACIÓN DE FORMULARIOS DE EMPRESA
       (crear_empresa.php / editar_empresa.php)
    ========================================================= */

    function validarCodigoEmpresa(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (!/^[0-9]{6}$/.test(texto)) {
            return 'Debe tener exactamente 6 dígitos.';
        }

        return null;

    }

    function validarNombreEmpresa(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (texto.length < 2) {
            return 'Debe tener al menos 2 caracteres.';
        }

        if (texto.length > 150) {
            return 'No puede superar los 150 caracteres.';
        }

        // Mismo criterio que validarCaracteresNombreEmpresa() en
        // includes/validaciones.php: una razón social permite,
        // además de letras/números/espacios, la puntuación
        // habitual en denominaciones comerciales (. , ' - & ( ) /).
        // Antes esta función no comprobaba los caracteres en
        // absoluto, así que un símbolo no permitido pasaba el
        // formulario sin avisar y el servidor lo rechazaba
        // después, obligando a otra vuelta.
        if (!/^[\p{L}\p{N}]/u.test(texto)) {
            return 'Debe empezar con una letra o un número.';
        }

        if (!/^[\p{L}\p{N}][\p{L}\p{N}\s'&.,()\/-]*$/u.test(texto)) {
            return 'Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.';
        }

        return null;

    }

    function validarCif(valor) {

        const texto = valor.trim().toUpperCase();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        // Mismo formato y dígito/letra de control que valida
        // validarCIF() en includes/validaciones.php, para que
        // un CIF con formato inválido no llegue a enviarse al
        // servidor y descubrirse solo allí (obligando a otra
        // vuelta si además había otro error en el formulario).

        const coincide = texto.match(/^([ABCDEFGHJNPQRSUVW])([0-9]{7})([0-9A-J])$/);

        if (!coincide) {
            return 'Introduce un CIF válido.';
        }

        const [, letraInicial, numeros, control] = coincide;

        let suma = 0;

        for (let i = 0; i < 7; i++) {

            const numero = Number(numeros[i]);

            if (i % 2 === 0) {

                let resultado = numero * 2;

                if (resultado >= 10) {
                    resultado = Math.floor(resultado / 10) + (resultado % 10);
                }

                suma += resultado;

            } else {

                suma += numero;

            }

        }

        const digitoControl = (10 - (suma % 10)) % 10;

        const letrasControl = 'JABCDEFGHI';
        const controlNumerico = String(digitoControl);
        const controlAlfabetico = letrasControl[digitoControl];

        let esValido;

        if (['A', 'B', 'E', 'H'].includes(letraInicial)) {
            esValido = control === controlNumerico;
        } else if (['K', 'P', 'Q', 'S'].includes(letraInicial)) {
            esValido = control === controlAlfabetico;
        } else {
            esValido = control === controlNumerico || control === controlAlfabetico;
        }

        if (!esValido) {
            return 'Introduce un CIF válido.';
        }

        return null;

    }

    function validarDireccionEmpresa(valor) {

        // La dirección es opcional
        return null;

    }

    function validarTelefonoEmpresa(valor) {

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

    function validarEmailEmpresa(valor) {

        const texto = valor.trim();

        // El email es opcional
        if (texto === '') {
            return null;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(texto)) {
            return 'Introduce un email con un formato válido.';
        }

        return null;

    }

    function validarEstadoEmpresa(valor) {

        if (!valor || valor.trim() === '') {
            return 'Debes seleccionar una opción.';
        }

        return null;

    }

    function validarMotivoInactivoEmpresa(valor) {

        const estado = document.getElementById('estado');

        if (estado && estado.value === 'Inactivo' && (!valor || valor.trim() === '')) {
            return 'Debes seleccionar un motivo.';
        }

        return null;

    }

    function validarUsuarioUsername(valor) {

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

    const VALIDADORES_EMPRESA = {
        codigo_empresa: validarCodigoEmpresa,
        nombre: validarNombreEmpresa,
        cif: validarCif,
        direccion: validarDireccionEmpresa,
        telefono: validarTelefonoEmpresa,
        email: validarEmailEmpresa,
        estado: validarEstadoEmpresa,
        motivo_inactivo: validarMotivoInactivoEmpresa,
        usuario_username: validarUsuarioUsername
    };

    function validarCampoEmpresa(input) {

        const validador = VALIDADORES_EMPRESA[input.name];

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

    function mostrarMensajeGeneralEmpresa(formulario, mensaje) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = mensaje;
        contenedor.style.display = 'block';

    }

    function ocultarMensajeGeneralEmpresa(formulario) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = '';
        contenedor.style.display = 'none';

    }

    function quedanCamposInvalidosEmpresa(formulario) {

        return Object.keys(VALIDADORES_EMPRESA).some(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return false;
            }

            return VALIDADORES_EMPRESA[nombreCampo](input.value) !== null;

        });

    }

    function inicializarValidacionFormularioEmpresa() {

        const formulario = document.querySelector('.config-card form');

        if (!formulario) {
            return;
        }

        Object.keys(VALIDADORES_EMPRESA).forEach(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return;
            }

            input.addEventListener('blur', () => {
                validarCampoEmpresa(input);
            });

            input.addEventListener('input', () => {

                const validador = VALIDADORES_EMPRESA[input.name];
                const error = validador(input.value);

                if (!error) {
                    input.classList.remove('input-error');
                    const contenedorError = document.getElementById('error-' + input.name);
                    if (contenedorError) {
                        contenedorError.textContent = '';
                    }
                }

                if (!quedanCamposInvalidosEmpresa(formulario)) {
                    ocultarMensajeGeneralEmpresa(formulario);
                }

            });

        });

        formulario.addEventListener('submit', (event) => {

            let formularioValido = true;

            Object.keys(VALIDADORES_EMPRESA).forEach(nombreCampo => {

                const input = formulario.querySelector('#' + nombreCampo);

                if (!input) {
                    return;
                }

                const campoValido = validarCampoEmpresa(input);

                if (!campoValido) {
                    formularioValido = false;
                }

            });

            if (!formularioValido) {

                event.preventDefault();

                mostrarMensajeGeneralEmpresa(
                    formulario,
                    'El formulario contiene errores. Revísalos antes de enviarlo.'
                );

                const mensajeGeneral = formulario.querySelector('#form-error-general');

                if (mensajeGeneral) {
                    mensajeGeneral.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

            } else {

                ocultarMensajeGeneralEmpresa(formulario);

                // Evita el doble envío (doble clic, o un segundo
                // clic porque la página tarda un instante en
                // navegar): sin esto, dos peticiones casi
                // simultáneas pueden colarse las dos antes de que
                // ninguna haya guardado nada todavía, la primera
                // crea la empresa y la segunda, al encontrarla ya
                // creada, responde con "el username/email/CIF/
                // código ya existe" — un error confuso, porque la
                // empresa SÍ se ha guardado (por la primera).
                const botonGuardar = formulario.querySelector('button[type="submit"]');

                if (botonGuardar) {
                    botonGuardar.disabled = true;
                }

            }

        });

    }

    inicializarValidacionFormularioEmpresa();


    /* =========================================================
       00B. MOTIVO DE INACTIVO
       El textarea solo se muestra (y solo hace falta
       rellenarlo) cuando el estado elegido es "Inactivo".
    ========================================================= */

    function actualizarVisibilidadMotivoInactivoEmpresa() {

        const estado = document.getElementById('estado');
        const grupoMotivo = document.getElementById('grupo_motivo_inactivo');

        if (!estado || !grupoMotivo) {
            return;
        }

        grupoMotivo.classList.toggle('hidden', estado.value !== 'Inactivo');

    }

    const estadoEmpresaSelect = document.getElementById('estado');

    if (estadoEmpresaSelect) {

        actualizarVisibilidadMotivoInactivoEmpresa();

        estadoEmpresaSelect.addEventListener('change', () => {

            actualizarVisibilidadMotivoInactivoEmpresa();

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
       01. ELEMENTOS DEL DOM (LISTADO)
    ========================================================= */

    const tbody = document.getElementById('empresasBody');
    const tabla = document.querySelector('.usuarios-table');

    const contador = document.getElementById('empresasContador');
    const mostrando = document.getElementById('empresasMostrando');

    const selectorPorPagina = document.getElementById('selectorPorPagina');

    // Los filtros por columna de escritorio y los del panel
    // móvil usan la misma clase y el mismo data-column, así
    // que un único selector los recoge a todos: solo el que
    // esté visible en cada momento tendrá valor.
    const filtros = document.querySelectorAll('.column-filter');

    const botonesOrden = document.querySelectorAll('.usuarios-table .sort-button');

    const botonesPaginacion = document.querySelectorAll('.usuarios-pagination .pagination-button');


    /* =========================================================
       02. COMPROBACIÓN DE ELEMENTOS
       (si no hay tabla, esta página no es el listado)
    ========================================================= */

    if (!tbody || !tabla) {
        return;
    }


    /* =========================================================
       03. CONFIGURACIÓN
    ========================================================= */

    let filas = Array.from(tbody.querySelectorAll('tr'));

    let EMPRESAS_POR_PAGINA = 5;

    let paginaActual = 1;

    let EMPRESAS_TOTALES = filas.length;

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

    function obtenerTextoCelda(fila, columna) {

        const celdas = fila.querySelectorAll('td');

        if (!celdas[columna]) {
            return '';
        }

        return normalizar(celdas[columna].textContent);

    }


    /* =========================================================
       06B. VALOR "LIMPIO" DE UNA COLUMNA PARA LOS FILTROS
       DESPLEGABLES
       =========================================================
       Usamos los data-* de la fila (ya traen el valor limpio
       de cada campo) en vez del texto de la celda, para que
       las opciones marcadas en el desplegable coincidan de
       forma exacta.
    ========================================================= */

    const CAMPO_POR_COLUMNA = {
        0: 'nombre',
        1: 'cif',
        2: 'direccion',
        3: 'telefono',
        4: 'email',
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

                const columna = Number(filtro.dataset.column);

                // Filtros desplegables de selección múltiple:
                // la fila pasa si su valor coincide con
                // CUALQUIERA de las opciones marcadas (si no
                // hay ninguna marcada, el filtro no se aplica).
                if (filtro.classList.contains('multi-select-filter')) {

                    const seleccionados = window.obtenerSeleccionMultiFiltro(filtro)
                        .map(normalizar);

                    if (seleccionados.length === 0) {
                        return;
                    }

                    const valorFila = obtenerValorFiltroFila(fila, columna);

                    if (!seleccionados.includes(valorFila)) {
                        coincide = false;
                    }

                    return;

                }

                const valorFiltro = normalizar(filtro.value);

                if (valorFiltro === '') {
                    return;
                }

                const valorCelda = obtenerTextoCelda(fila, columna);

                if (!valorCelda.includes(valorFiltro)) {
                    coincide = false;
                }

            });

            return coincide;

        });

    }


    /* =========================================================
       08. TOTAL DE PÁGINAS
    ========================================================= */

    function obtenerTotalPaginas(filasFiltradas) {

        if (filasFiltradas.length === 0) {
            return 1;
        }

        if (EMPRESAS_POR_PAGINA === Infinity) {
            return 1;
        }

        return Math.ceil(filasFiltradas.length / EMPRESAS_POR_PAGINA);

    }


    /* =========================================================
       09. MOSTRAR PÁGINA
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

        const inicio = EMPRESAS_POR_PAGINA === Infinity
            ? 0
            : (paginaActual - 1) * EMPRESAS_POR_PAGINA;

        const fin = EMPRESAS_POR_PAGINA === Infinity
            ? filasFiltradas.length
            : inicio + EMPRESAS_POR_PAGINA;

        const filasPagina = filasFiltradas.slice(inicio, fin);

        filasPagina.forEach(fila => {
            fila.style.display = '';
        });

        actualizarContadores(filasFiltradas);
        actualizarPaginacion(totalPaginas);

    }


    /* =========================================================
       10. CONTADORES
    ========================================================= */

    function actualizarContadores(filasFiltradas) {

        const cantidadFiltrada = filasFiltradas.length;

        if (contador) {

            contador.textContent = cantidadFiltrada === 1
                ? '1 empresa encontrada'
                : `${cantidadFiltrada} empresas encontradas`;

        }

        if (mostrando) {

            if (cantidadFiltrada === 0) {

                mostrando.textContent = `Mostrando 0 de ${EMPRESAS_TOTALES} empresas`;

                return;

            }

            if (EMPRESAS_POR_PAGINA === Infinity) {

                mostrando.textContent = `Mostrando ${cantidadFiltrada} de ${cantidadFiltrada} empresas`;

                return;

            }

            const inicio = (paginaActual - 1) * EMPRESAS_POR_PAGINA + 1;

            const fin = Math.min(
                inicio + EMPRESAS_POR_PAGINA - 1,
                cantidadFiltrada
            );

            mostrando.textContent = `Mostrando ${inicio}-${fin} de ${cantidadFiltrada} empresas`;

        }

    }


    /* =========================================================
       11. PAGINACIÓN
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
                    (EMPRESAS_POR_PAGINA === Infinity || numeroPagina > totalPaginas)
                        ? 'none'
                        : 'inline-flex';

                boton.classList.toggle('active', numeroPagina === paginaActual);

            }

        });

    }


    /* =========================================================
       12. EVENTOS DE PAGINACIÓN
    ========================================================= */

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
       12B. RECORDAR FILTROS ENTRE RECARGAS
       =========================================================
       Activar/desactivar una empresa desde el listado navega a
       cambiar_estado_empresa.php, que vuelve a redirigir aquí:
       la página se recarga entera y, sin esto, los filtros
       marcados se perderían. Se guardan en sessionStorage (no
       localStorage) para que no sobrevivan más allá de la
       pestaña/sesión actual del navegador.
    ========================================================= */

    const CLAVE_FILTROS_GUARDADOS = 'filtrosEmpresas';

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

        filtro.addEventListener('input', () => {
            guardarFiltros();
            paginaActual = 1;
            mostrarPagina();
        });

        filtro.addEventListener('change', () => {
            guardarFiltros();
            paginaActual = 1;
            mostrarPagina();
        });

    });


    /* =========================================================
       14. LIMPIAR FILTROS
       Hay dos botones "Limpiar filtros" (el de la tabla de
       escritorio y el del panel móvil); los dos vacían el
       mismo conjunto de inputs, escritorio y móvil incluidos.
    ========================================================= */

    document.querySelectorAll('#btnLimpiarFiltros, #btnLimpiarFiltrosMovil').forEach(btn => {

        btn.addEventListener('click', () => {

            filtros.forEach(filtro => {

                if (filtro.classList.contains('multi-select-filter')) {
                    window.limpiarMultiFiltro(filtro);
                } else {
                    filtro.value = '';
                }

            });

            olvidarFiltrosGuardados();

            paginaActual = 1;
            mostrarPagina();

        });

    });


    /* =========================================================
       14B. EXPORTAR PDF
       Exporta las empresas que cumplen los filtros activos
       (ver js/exportar-pdf.js).
    ========================================================= */

    const btnExportarPDF = document.getElementById('btnExportarPDF');

    if (btnExportarPDF) {

        btnExportarPDF.addEventListener('click', () => {
            exportarListadoPDF('exportar_pdf.php', obtenerFilasFiltradas);
        });

    }


    /* =========================================================
       15. EMPRESAS POR PÁGINA
    ========================================================= */

    if (selectorPorPagina) {

        selectorPorPagina.addEventListener('change', () => {

            const valor = selectorPorPagina.value;

            EMPRESAS_POR_PAGINA = valor === 'todos'
                ? Infinity
                : Number(valor);

            paginaActual = 1;
            mostrarPagina();

        });

    }


    /* =========================================================
       16. ORDENACIÓN
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

                    const valorA = obtenerTextoCelda(a, columna);
                    const valorB = obtenerTextoCelda(b, columna);

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
       16B. PANEL DE FILTROS DESPLEGABLE (SOLO MÓVIL)
    ========================================================= */

    const btnToggleFiltros = document.getElementById('btnToggleFiltros');
    const panelFiltros = document.getElementById('panelFiltrosEmpresas');

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
       =========================================================
       En escritorio, tocar la fila no hace nada — ahí ya se
       ve todo y están los botones Editar/Eliminar de siempre.
    ========================================================= */

    const modalDetalle = document.getElementById('modalDetalleEmpresa');

    const detalleAvatar = document.getElementById('detalleEmpresaAvatar');
    const detalleNombre = document.getElementById('detalleEmpresaNombre');
    const detalleCodigo = document.getElementById('detalleEmpresaCodigo');
    const detalleCif = document.getElementById('detalleEmpresaCif');
    const detalleDireccion = document.getElementById('detalleEmpresaDireccion');
    const detalleTelefono = document.getElementById('detalleEmpresaTelefono');
    const detalleEmail = document.getElementById('detalleEmpresaEmail');
    const detalleEstado = document.getElementById('detalleEmpresaEstado');
    const detalleMotivo = document.getElementById('detalleEmpresaMotivo');
    const detalleMotivoItem = document.getElementById('detalleEmpresaMotivoItem');
    const btnDetalleEditar = document.getElementById('btnDetalleEditarEmpresa');
    const btnDetalleEliminar = document.getElementById('btnDetalleEliminarEmpresa');

    let empresaDetalleActual = null;

    function abrirModalDetalleEmpresa(fila) {

        empresaDetalleActual = fila.dataset;

        if (detalleAvatar) {
            detalleAvatar.textContent = fila.dataset.iniciales || '';
        }

        if (detalleNombre) {
            detalleNombre.textContent = fila.dataset.nombre || '';
        }

        if (detalleCodigo) {
            detalleCodigo.textContent = fila.dataset.codigo || '—';
        }

        if (detalleCif) {
            detalleCif.textContent = fila.dataset.cif || '—';
        }

        if (detalleDireccion) {
            detalleDireccion.textContent = fila.dataset.direccion || '—';
        }

        if (detalleTelefono) {
            detalleTelefono.textContent = fila.dataset.telefono || '—';
        }

        if (detalleEmail) {
            detalleEmail.textContent = fila.dataset.email || '—';
        }

        if (detalleEstado) {
            detalleEstado.textContent = fila.dataset.estado || '—';
            detalleEstado.classList.toggle('text-success', fila.dataset.estado === 'Activo');
            detalleEstado.classList.toggle('text-danger', fila.dataset.estado === 'Inactivo');
        }

        if (detalleMotivo) {
            detalleMotivo.textContent = fila.dataset.motivo || '-';
        }

        if (detalleMotivoItem) {
            detalleMotivoItem.classList.toggle('hidden', fila.dataset.estado !== 'Inactivo');
        }

        if (btnDetalleEditar) {
            btnDetalleEditar.href = 'editar_empresa.php?id=' + encodeURIComponent(fila.dataset.id);
        }

        if (modalDetalle) {
            modalDetalle.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    }

    window.cerrarModalDetalleEmpresa = function () {

        empresaDetalleActual = null;

        if (modalDetalle) {
            modalDetalle.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    filas.forEach(fila => {

        fila.addEventListener('click', () => {
            abrirModalDetalleEmpresa(fila);
        });

    });

    if (btnDetalleEliminar) {

        btnDetalleEliminar.addEventListener('click', () => {

            if (!empresaDetalleActual) {
                return;
            }

            const id = empresaDetalleActual.id;
            const nombre = empresaDetalleActual.nombre;

            window.cerrarModalDetalleEmpresa();
            window.abrirModalEliminarEmpresa(id, nombre);

        });

    }

    if (modalDetalle) {

        modalDetalle.addEventListener('click', event => {

            if (event.target === modalDetalle) {
                window.cerrarModalDetalleEmpresa();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && empresaDetalleActual) {
            window.cerrarModalDetalleEmpresa();
        }

    });


    /* =========================================================
       17. MODAL DE ELIMINACIÓN
    ========================================================= */

    let empresaEliminarId = 0;

    const modalEliminar = document.getElementById('modalEliminarEmpresa');
    const nombreEmpresaEliminar = document.getElementById('nombreEmpresaEliminar');


    window.abrirModalEliminarEmpresa = function (id, nombre) {

        empresaEliminarId = Number(id);

        if (nombreEmpresaEliminar) {
            nombreEmpresaEliminar.textContent = nombre;
        }

        if (modalEliminar) {
            modalEliminar.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };


    window.cerrarModalEliminarEmpresa = function () {

        empresaEliminarId = 0;

        if (modalEliminar) {
            modalEliminar.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };


    let empresaEliminarEnCurso = false;

    window.confirmarEliminarEmpresa = async function () {

        if (empresaEliminarId <= 0 || empresaEliminarEnCurso) {
            return;
        }

        const idEliminado = empresaEliminarId;

        empresaEliminarEnCurso = true;

        try {

            const respuesta = await fetch(
                'eliminar_empresa.php?id=' + encodeURIComponent(idEliminado) + '&ajax=1',
                { headers: { Accept: 'application/json' } }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(datos.error || 'No se ha podido eliminar la empresa.');
            }

            window.cerrarModalEliminarEmpresa();

            const fila = tbody.querySelector(`tr[data-id="${idEliminado}"]`);

            if (fila) {
                fila.remove();
                filas = filas.filter(filaActual => filaActual !== fila);
                EMPRESAS_TOTALES = filas.length;
                mostrarPagina();
            }

            mostrarNotificacionEliminacion(datos);

        } catch (error) {
            window.alert(error.message);
        } finally {
            empresaEliminarEnCurso = false;
        }

    };


    if (modalEliminar) {

        modalEliminar.addEventListener('click', event => {

            if (event.target === modalEliminar) {
                window.cerrarModalEliminarEmpresa();
            }

        });

    }


    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && empresaEliminarId > 0) {
            window.cerrarModalEliminarEmpresa();
        }

    });


    /* =========================================================
       17B. DESACTIVAR EMPRESA DESDE EL LISTADO (PIDE MOTIVO)
       Mismo patrón que el modal de desactivar usuario: al
       llevarse el motivo, se envía un formulario real por POST
       a cambiar_estado_empresa.php en vez de ir directo.
    ========================================================= */

    let empresaDesactivarAbierto = false;

    const modalDesactivarEmpresa = document.getElementById('modalDesactivarEmpresa');
    const nombreEmpresaDesactivar = document.getElementById('nombreEmpresaDesactivar');
    const idEmpresaDesactivarInput = document.getElementById('idEmpresaDesactivar');
    const motivoDesactivarEmpresa = document.getElementById('motivoDesactivarEmpresa');

    window.abrirModalDesactivarEmpresa = function (id, nombre) {

        empresaDesactivarAbierto = true;

        if (idEmpresaDesactivarInput) {
            idEmpresaDesactivarInput.value = id;
        }

        if (nombreEmpresaDesactivar) {
            nombreEmpresaDesactivar.textContent = nombre;
        }

        if (motivoDesactivarEmpresa) {
            motivoDesactivarEmpresa.value = '';
            motivoDesactivarEmpresa.classList.remove('input-error');
        }

        const contenedorError = document.getElementById('error-motivoDesactivarEmpresa');
        if (contenedorError) {
            contenedorError.textContent = '';
        }

        if (modalDesactivarEmpresa) {
            modalDesactivarEmpresa.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };

    window.cerrarModalDesactivarEmpresa = function () {

        empresaDesactivarAbierto = false;

        if (modalDesactivarEmpresa) {
            modalDesactivarEmpresa.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    const formDesactivarEmpresa = document.getElementById('formDesactivarEmpresa');

    if (formDesactivarEmpresa && motivoDesactivarEmpresa) {

        formDesactivarEmpresa.addEventListener('submit', event => {

            if (motivoDesactivarEmpresa.value === '') {

                event.preventDefault();

                motivoDesactivarEmpresa.classList.add('input-error');

                const contenedorError = document.getElementById('error-motivoDesactivarEmpresa');
                if (contenedorError) {
                    contenedorError.textContent = 'Debes seleccionar un motivo.';
                }

                motivoDesactivarEmpresa.focus();

            }

        });

        motivoDesactivarEmpresa.addEventListener('blur', () => {

            // Al salir sin elegir, mismo aviso que al enviar.
            if (motivoDesactivarEmpresa.value === '') {
                motivoDesactivarEmpresa.classList.add('input-error');
                const contenedorError = document.getElementById('error-motivoDesactivarEmpresa');
                if (contenedorError) {
                    contenedorError.textContent = 'Debes seleccionar un motivo.';
                }
            }

        });

        motivoDesactivarEmpresa.addEventListener('change', () => {

            motivoDesactivarEmpresa.classList.remove('input-error');

            const contenedorError = document.getElementById('error-motivoDesactivarEmpresa');
            if (contenedorError) {
                contenedorError.textContent = '';
            }

        });

    }

    if (modalDesactivarEmpresa) {

        modalDesactivarEmpresa.addEventListener('click', event => {

            if (event.target === modalDesactivarEmpresa) {
                window.cerrarModalDesactivarEmpresa();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && empresaDesactivarAbierto) {
            window.cerrarModalDesactivarEmpresa();
        }

    });


    /* =========================================================
       17C. RESTABLECER CONTRASEÑA DEL USUARIO DE LA EMPRESA
       Mismo patrón que el modal de eliminación: un único
       modal reutilizado por todas las filas, con el id del
       usuario objetivo guardado en una variable hasta que
       se confirma o se cancela. Reutiliza el mismo endpoint
       que el listado de usuarios (resetear_password.php),
       ya que lo que se restablece es la contraseña del
       usuario de acceso de la empresa, no la empresa en sí.
    ========================================================= */

    let empresaResetPasswordUsuarioId = 0;

    const modalResetPasswordEmpresa = document.getElementById('modalResetPasswordEmpresa');
    const nombreEmpresaResetPassword = document.getElementById('nombreEmpresaResetPassword');

    window.abrirModalResetPasswordEmpresa = function (idUsuario, nombre) {

        empresaResetPasswordUsuarioId = Number(idUsuario);

        if (nombreEmpresaResetPassword) {
            nombreEmpresaResetPassword.textContent = nombre;
        }

        if (modalResetPasswordEmpresa) {
            modalResetPasswordEmpresa.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };

    window.cerrarModalResetPasswordEmpresa = function () {

        empresaResetPasswordUsuarioId = 0;

        if (modalResetPasswordEmpresa) {
            modalResetPasswordEmpresa.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    window.confirmarResetPasswordEmpresa = function () {

        if (empresaResetPasswordUsuarioId <= 0) {
            return;
        }

        window.location.href =
            '../usuarios/resetear_password.php?id=' + encodeURIComponent(empresaResetPasswordUsuarioId);

    };

    if (modalResetPasswordEmpresa) {

        modalResetPasswordEmpresa.addEventListener('click', event => {

            if (event.target === modalResetPasswordEmpresa) {
                window.cerrarModalResetPasswordEmpresa();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && empresaResetPasswordUsuarioId > 0) {
            window.cerrarModalResetPasswordEmpresa();
        }

    });


    /* =========================================================
       18. PAGINACIÓN INICIAL
    ========================================================= */

    restaurarFiltrosGuardados();
    mostrarPagina();

});

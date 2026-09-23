document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       00. VALIDACIÓN DE FORMULARIOS DE COMERCIALIZADORA
       (crear_comercializadora.php / editar_comercializadora.php)
    ========================================================= */

    function validarNombreComercializadora(valor) {

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
        // includes/validaciones.php: además de letras/números/
        // espacios, permite la puntuación habitual en
        // denominaciones comerciales (. , ' - & ( ) /).
        if (!/^[\p{L}\p{N}]/u.test(texto)) {
            return 'Debe empezar con una letra o un número.';
        }

        if (!/^[\p{L}\p{N}][\p{L}\p{N}\s'&.,()\/-]*$/u.test(texto)) {
            return 'Solo se permiten letras, números, espacios y los símbolos . , \' - & ( ) /.';
        }

        return null;

    }

    function validarCifComercializadora(valor) {

        const texto = valor.trim().toUpperCase();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        // Mismo formato y dígito/letra de control que valida
        // validarCIF() en includes/validaciones.php, para que
        // un CIF con formato inválido no llegue a enviarse al
        // servidor y descubrirse solo allí.

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

    function validarDireccionComercializadora(valor) {

        // La dirección es opcional
        return null;

    }

    function validarTelefonoComercializadora(valor) {

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

    function validarEmailComercializadora(valor) {

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

    const VALIDADORES_COMERCIALIZADORA = {
        nombre: validarNombreComercializadora,
        cif: validarCifComercializadora,
        direccion: validarDireccionComercializadora,
        telefono: validarTelefonoComercializadora,
        email: validarEmailComercializadora
    };

    function validarCampoComercializadora(input) {

        const validador = VALIDADORES_COMERCIALIZADORA[input.name];

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

    function mostrarMensajeGeneralComercializadora(formulario, mensaje) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = mensaje;
        contenedor.style.display = 'block';

    }

    function ocultarMensajeGeneralComercializadora(formulario) {

        const contenedor = formulario.querySelector('#form-error-general');

        if (!contenedor) {
            return;
        }

        contenedor.textContent = '';
        contenedor.style.display = 'none';

    }

    function quedanCamposInvalidosComercializadora(formulario) {

        return Object.keys(VALIDADORES_COMERCIALIZADORA).some(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return false;
            }

            return VALIDADORES_COMERCIALIZADORA[nombreCampo](input.value) !== null;

        });

    }

    /* =========================================================
       00B. SERVICIOS (LUZ / GAS)
       No es un campo de texto como los demás: hay que marcar
       al menos una de las dos casillas.
    ========================================================= */

    function validarServiciosComercializadora(formulario) {

        const luz = formulario.querySelector('#suministra_luz');
        const gas = formulario.querySelector('#suministra_gas');

        if (!luz || !gas) {
            return true;
        }

        const contenedorError = document.getElementById('error-suministra_luz');
        const valido = luz.checked || gas.checked;

        if (!valido) {

            if (contenedorError) {
                contenedorError.textContent = 'Debes marcar al menos un servicio (luz o gas).';
            }

            return false;

        }

        if (contenedorError) {
            contenedorError.textContent = '';
        }

        return true;

    }

    function inicializarValidacionFormularioComercializadora() {

        const formulario = document.querySelector('.config-card form');

        if (!formulario) {
            return;
        }

        Object.keys(VALIDADORES_COMERCIALIZADORA).forEach(nombreCampo => {

            const input = formulario.querySelector('#' + nombreCampo);

            if (!input) {
                return;
            }

            input.addEventListener('blur', () => {
                validarCampoComercializadora(input);
            });

            input.addEventListener('input', () => {

                const validador = VALIDADORES_COMERCIALIZADORA[input.name];
                const error = validador(input.value);

                if (!error) {
                    input.classList.remove('input-error');
                    const contenedorError = document.getElementById('error-' + input.name);
                    if (contenedorError) {
                        contenedorError.textContent = '';
                    }
                }

                if (!quedanCamposInvalidosComercializadora(formulario)) {
                    ocultarMensajeGeneralComercializadora(formulario);
                }

            });

        });

        formulario.querySelectorAll('#suministra_luz, #suministra_gas').forEach(checkbox => {

            checkbox.addEventListener('change', () => {
                validarServiciosComercializadora(formulario);
            });

        });

        formulario.addEventListener('submit', (event) => {

            let formularioValido = true;

            Object.keys(VALIDADORES_COMERCIALIZADORA).forEach(nombreCampo => {

                const input = formulario.querySelector('#' + nombreCampo);

                if (!input) {
                    return;
                }

                const campoValido = validarCampoComercializadora(input);

                if (!campoValido) {
                    formularioValido = false;
                }

            });

            if (!validarServiciosComercializadora(formulario)) {
                formularioValido = false;
            }

            if (!formularioValido) {

                event.preventDefault();

                mostrarMensajeGeneralComercializadora(
                    formulario,
                    'Hay campos obligatorios sin completar o con un formato incorrecto. Revisa los campos marcados en rojo.'
                );

                const mensajeGeneral = formulario.querySelector('#form-error-general');

                if (mensajeGeneral) {
                    mensajeGeneral.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

            } else {

                ocultarMensajeGeneralComercializadora(formulario);

                // Evita el doble envío (doble clic, o un segundo
                // clic porque la página tarda un instante en
                // navegar).
                const botonGuardar = formulario.querySelector('button[type="submit"]');

                if (botonGuardar) {
                    botonGuardar.disabled = true;
                }

            }

        });

    }

    inicializarValidacionFormularioComercializadora();


    /* =========================================================
       01. ELEMENTOS DEL DOM (LISTADO)
    ========================================================= */

    const tbody = document.getElementById('comercializadorasBody');
    const tabla = document.querySelector('.usuarios-table');

    const contador = document.getElementById('comercializadorasContador');
    const mostrando = document.getElementById('comercializadorasMostrando');

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

    let COMERCIALIZADORAS_POR_PAGINA = 5;

    let paginaActual = 1;

    let COMERCIALIZADORAS_TOTALES = filas.length;

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
    ========================================================= */

    const CAMPO_POR_COLUMNA = {
        0: 'nombre',
        1: 'cif',
        2: 'direccion',
        3: 'telefono',
        4: 'email',
        5: 'servicios'
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

        if (COMERCIALIZADORAS_POR_PAGINA === Infinity) {
            return 1;
        }

        return Math.ceil(filasFiltradas.length / COMERCIALIZADORAS_POR_PAGINA);

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

        const inicio = COMERCIALIZADORAS_POR_PAGINA === Infinity
            ? 0
            : (paginaActual - 1) * COMERCIALIZADORAS_POR_PAGINA;

        const fin = COMERCIALIZADORAS_POR_PAGINA === Infinity
            ? filasFiltradas.length
            : inicio + COMERCIALIZADORAS_POR_PAGINA;

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
                ? '1 comercializadora encontrada'
                : `${cantidadFiltrada} comercializadoras encontradas`;

        }

        if (mostrando) {

            if (cantidadFiltrada === 0) {

                mostrando.textContent = `Mostrando 0 de ${COMERCIALIZADORAS_TOTALES} comercializadoras`;

                return;

            }

            if (COMERCIALIZADORAS_POR_PAGINA === Infinity) {

                mostrando.textContent = `Mostrando ${cantidadFiltrada} de ${cantidadFiltrada} comercializadoras`;

                return;

            }

            const inicio = (paginaActual - 1) * COMERCIALIZADORAS_POR_PAGINA + 1;

            const fin = Math.min(
                inicio + COMERCIALIZADORAS_POR_PAGINA - 1,
                cantidadFiltrada
            );

            mostrando.textContent = `Mostrando ${inicio}-${fin} de ${cantidadFiltrada} comercializadoras`;

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
                    (COMERCIALIZADORAS_POR_PAGINA === Infinity || numeroPagina > totalPaginas)
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
    ========================================================= */

    const CLAVE_FILTROS_GUARDADOS = 'filtrosComercializadoras';

    function guardarFiltros() {

        const datos = {};

        filtros.forEach(filtro => {

            const columna = filtro.dataset.column;

            const valor = filtro.classList.contains('multi-select-filter')
                ? window.obtenerSeleccionMultiFiltro(filtro)
                : filtro.value;

            const vacio = Array.isArray(valor) ? valor.length === 0 : valor === '';

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
    ========================================================= */

    const btnExportarPDF = document.getElementById('btnExportarPDF');

    if (btnExportarPDF) {

        btnExportarPDF.addEventListener('click', () => {
            exportarListadoPDF('exportar_pdf.php', obtenerFilasFiltradas);
        });

    }


    /* =========================================================
       15. COMERCIALIZADORAS POR PÁGINA
    ========================================================= */

    if (selectorPorPagina) {

        selectorPorPagina.addEventListener('change', () => {

            const valor = selectorPorPagina.value;

            COMERCIALIZADORAS_POR_PAGINA = valor === 'todos'
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
    const panelFiltros = document.getElementById('panelFiltrosComercializadoras');

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

    const modalDetalle = document.getElementById('modalDetalleComercializadora');

    const detalleAvatar = document.getElementById('detalleComercializadoraAvatar');
    const detalleNombre = document.getElementById('detalleComercializadoraNombre');
    const detalleCif = document.getElementById('detalleComercializadoraCif');
    const detalleDireccion = document.getElementById('detalleComercializadoraDireccion');
    const detalleTelefono = document.getElementById('detalleComercializadoraTelefono');
    const detalleEmail = document.getElementById('detalleComercializadoraEmail');
    const detalleServicios = document.getElementById('detalleComercializadoraServicios');
    const btnDetalleEditar = document.getElementById('btnDetalleEditarComercializadora');
    const btnDetalleEliminar = document.getElementById('btnDetalleEliminarComercializadora');

    let comercializadoraDetalleActual = null;

    function abrirModalDetalleComercializadora(fila) {

        comercializadoraDetalleActual = fila.dataset;

        if (detalleAvatar) {
            detalleAvatar.textContent = fila.dataset.iniciales || '';
        }

        if (detalleNombre) {
            detalleNombre.textContent = fila.dataset.nombre || '';
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

        if (detalleServicios) {
            detalleServicios.textContent = fila.dataset.servicios || '—';
        }

        if (btnDetalleEditar) {
            btnDetalleEditar.href = 'editar_comercializadora.php?id=' + encodeURIComponent(fila.dataset.id);
        }

        if (modalDetalle) {
            modalDetalle.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    }

    window.cerrarModalDetalleComercializadora = function () {

        comercializadoraDetalleActual = null;

        if (modalDetalle) {
            modalDetalle.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };

    filas.forEach(fila => {

        fila.addEventListener('click', () => {
            abrirModalDetalleComercializadora(fila);
        });

    });

    if (btnDetalleEliminar) {

        btnDetalleEliminar.addEventListener('click', () => {

            if (!comercializadoraDetalleActual) {
                return;
            }

            const id = comercializadoraDetalleActual.id;
            const nombre = comercializadoraDetalleActual.nombre;

            window.cerrarModalDetalleComercializadora();
            window.abrirModalEliminarComercializadora(id, nombre);

        });

    }

    if (modalDetalle) {

        modalDetalle.addEventListener('click', event => {

            if (event.target === modalDetalle) {
                window.cerrarModalDetalleComercializadora();
            }

        });

    }

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && comercializadoraDetalleActual) {
            window.cerrarModalDetalleComercializadora();
        }

    });


    /* =========================================================
       17. MODAL DE ELIMINACIÓN
    ========================================================= */

    let comercializadoraEliminarId = 0;

    const modalEliminar = document.getElementById('modalEliminarComercializadora');
    const nombreComercializadoraEliminar = document.getElementById('nombreComercializadoraEliminar');


    window.abrirModalEliminarComercializadora = function (id, nombre) {

        comercializadoraEliminarId = Number(id);

        if (nombreComercializadoraEliminar) {
            nombreComercializadoraEliminar.textContent = nombre;
        }

        if (modalEliminar) {
            modalEliminar.style.display = 'flex';
            document.body.classList.add('modal-abierto');
        }

    };


    window.cerrarModalEliminarComercializadora = function () {

        comercializadoraEliminarId = 0;

        if (modalEliminar) {
            modalEliminar.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

    };


    let comercializadoraEliminarEnCurso = false;

    window.confirmarEliminarComercializadora = async function () {

        if (comercializadoraEliminarId <= 0 || comercializadoraEliminarEnCurso) {
            return;
        }

        const idEliminado = comercializadoraEliminarId;

        comercializadoraEliminarEnCurso = true;

        try {

            const respuesta = await fetch(
                'eliminar_comercializadora.php?id=' + encodeURIComponent(idEliminado) + '&ajax=1',
                { headers: { Accept: 'application/json' } }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(datos.error || 'No se ha podido eliminar la comercializadora.');
            }

            window.cerrarModalEliminarComercializadora();

            const fila = tbody.querySelector(`tr[data-id="${idEliminado}"]`);

            if (fila) {
                fila.remove();
                filas = filas.filter(filaActual => filaActual !== fila);
                COMERCIALIZADORAS_TOTALES = filas.length;
                mostrarPagina();
            }

            mostrarNotificacionEliminacion(datos);

        } catch (error) {
            window.alert(error.message);
        } finally {
            comercializadoraEliminarEnCurso = false;
        }

    };


    if (modalEliminar) {

        modalEliminar.addEventListener('click', event => {

            if (event.target === modalEliminar) {
                window.cerrarModalEliminarComercializadora();
            }

        });

    }


    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && comercializadoraEliminarId > 0) {
            window.cerrarModalEliminarComercializadora();
        }

    });


    /* =========================================================
       18. PAGINACIÓN INICIAL
    ========================================================= */

    restaurarFiltrosGuardados();
    mostrarPagina();

});

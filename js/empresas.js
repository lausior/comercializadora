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

        return null;

    }

    function validarCif(valor) {

        const texto = valor.trim();

        if (texto === '') {
            return 'Este campo es obligatorio.';
        }

        if (!/^[A-Za-z0-9]{9}$/.test(texto)) {
            return 'Introduce un CIF/NIF válido (9 caracteres).';
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

    const VALIDADORES_EMPRESA = {
        codigo_empresa: validarCodigoEmpresa,
        nombre: validarNombreEmpresa,
        cif: validarCif,
        direccion: validarDireccionEmpresa,
        telefono: validarTelefonoEmpresa,
        email: validarEmailEmpresa
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
                    'Hay campos obligatorios sin completar o con un formato incorrecto. Revisa los campos marcados en rojo.'
                );

                const mensajeGeneral = formulario.querySelector('#form-error-general');

                if (mensajeGeneral) {
                    mensajeGeneral.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

            } else {

                ocultarMensajeGeneralEmpresa(formulario);

            }

        });

    }

    inicializarValidacionFormularioEmpresa();


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

    const EMPRESAS_TOTALES = filas.length;

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
       07. FILTRADO
    ========================================================= */

    function obtenerFilasFiltradas() {

        return filas.filter(fila => {

            let coincide = true;

            filtros.forEach(filtro => {

                const columna = Number(filtro.dataset.column);
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
       13. FILTROS
    ========================================================= */

    filtros.forEach(filtro => {

        filtro.addEventListener('input', () => {
            paginaActual = 1;
            mostrarPagina();
        });

        filtro.addEventListener('change', () => {
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
                filtro.value = '';
            });

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
            detalleCodigo.textContent = 'Código ' + (fila.dataset.codigo || '');
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

            // En escritorio la fila no es clicable: ahí ya
            // se ve todo en la propia tabla.
            if (!esMovil()) {
                return;
            }

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


    window.confirmarEliminarEmpresa = function () {

        if (empresaEliminarId <= 0) {
            return;
        }

        window.location.href =
            'eliminar_empresa.php?id=' + encodeURIComponent(empresaEliminarId);

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
       18. PAGINACIÓN INICIAL
    ========================================================= */

    mostrarPagina();

});

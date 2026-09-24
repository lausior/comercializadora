/* =========================================================
   TARIFAS: REJILLAS TIPO EXCEL (views/tarifas/tarifas.php)
   =========================================================
   La página tiene dos tablas con el mismo funcionamiento,
   cada una en su propio <form class="tarifas-form">:
     - "Tarifa del cliente" (desplegable, botón de arriba)
     - "Tarifas de las comercializadoras"
   Filas = tarifas de cada titular (cliente o
   comercializadora), columnas = precios del peaje de la
   pestaña y, si la tabla los lleva, datos de factura. Cada
   tabla se guarda de una vez con su botón Guardar
   (guardar_tarifas.php).
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const rejillas = Array.from(document.querySelectorAll('form.tarifas-form'))
        .map(inicializarRejillaTarifas)
        .filter(Boolean);

    inicializarModalEliminarTarifa();
    inicializarPanelCliente();

    // Aviso al salir de la página (o cambiar de peaje/servicio)
    // con cambios sin guardar en cualquiera de las tablas.
    window.addEventListener('beforeunload', event => {

        if (rejillas.some(rejilla => rejilla.hayCambios() && !rejilla.enviando())) {
            event.preventDefault();
            event.returnValue = '';
        }

    });

});


/* =========================================================
   MODAL DE ELIMINAR (COMÚN A LAS DOS TABLAS)
   =========================================================
   Cada rejilla lo abre con abrirModalEliminarTarifa(), que
   recibe qué hacer con la fila una vez borrada en el
   servidor.
========================================================= */

let tarifaEliminar = null;

function abrirModalEliminarTarifa(fila, nombre, esCliente, alEliminar) {

    const modal = document.getElementById('modalEliminarTarifa');

    tarifaEliminar = { fila, alEliminar };

    document.getElementById('nombreTarifaEliminar').textContent = nombre;

    // "Desmarca Activa" solo tiene sentido en las tarifas de
    // comercializadoras (las de cliente no tienen esa casilla).
    document.getElementById('pistaEliminarTarifa').hidden = esCliente;

    modal.style.display = 'flex';
    document.body.classList.add('modal-abierto');

}

function inicializarModalEliminarTarifa() {

    const modal = document.getElementById('modalEliminarTarifa');

    if (!modal) {
        return;
    }

    let eliminacionEnCurso = false;

    function cerrar() {

        tarifaEliminar = null;

        modal.style.display = 'none';
        document.body.classList.remove('modal-abierto');

    }

    async function confirmar() {

        if (!tarifaEliminar || eliminacionEnCurso) {
            return;
        }

        const { fila, alEliminar } = tarifaEliminar;

        eliminacionEnCurso = true;

        try {

            const cuerpo = new FormData();
            cuerpo.append('id', fila.dataset.id);

            const respuesta = await fetch('eliminar_tarifa.php', {
                method: 'POST',
                body: cuerpo,
                headers: { Accept: 'application/json' },
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                throw new Error(datos.error || 'No se ha podido eliminar la tarifa.');
            }

            cerrar();
            alEliminar(fila);

            if (typeof window.mostrarNotificacionEliminacion === 'function') {
                window.mostrarNotificacionEliminacion(datos);
            }

        } catch (error) {
            window.alert(error.message);
        } finally {
            eliminacionEnCurso = false;
        }

    }

    document.getElementById('btnConfirmarEliminarTarifa').addEventListener('click', confirmar);
    document.getElementById('btnCancelarEliminarTarifa').addEventListener('click', cerrar);

    modal.addEventListener('click', event => {

        if (event.target === modal) {
            cerrar();
        }

    });

    document.addEventListener('keydown', event => {

        if (event.key === 'Escape' && tarifaEliminar) {
            cerrar();
        }

    });

}


/* =========================================================
   BOTÓN "TARIFA DEL CLIENTE" Y "COMPARAR"
   =========================================================
   Despliega / oculta la tabla del cliente encima de la de
   comercializadoras sin recargar. El estado se refleja en la
   URL (cliente=1), en los enlaces de luz/gas y de peaje
   (data-enlace-tarifas) y en el campo cliente_abierto de los
   formularios, para que siga igual al navegar o al guardar.
========================================================= */

function inicializarPanelCliente() {

    const boton = document.getElementById('btnTarifaCliente');
    const panel = document.getElementById('panelTarifaCliente');

    if (!boton || !panel) {
        return;
    }

    function ponerParametroCliente(url, abierto) {

        if (abierto) {
            url.searchParams.set('cliente', '1');
        } else {
            url.searchParams.delete('cliente');
        }

        return url;

    }

    function aplicarEstado(abierto) {

        panel.hidden = !abierto;
        boton.classList.toggle('activo', abierto);
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');

        document.querySelectorAll('a[data-enlace-tarifas]').forEach(enlace => {
            enlace.href = ponerParametroCliente(new URL(enlace.href), abierto).toString();
        });

        document.querySelectorAll('input[name="cliente_abierto"]').forEach(campo => {
            campo.value = abierto ? '1' : '0';
        });

        window.history.replaceState(null, '', ponerParametroCliente(new URL(window.location.href), abierto).toString());

    }

    boton.addEventListener('click', () => {

        const abrir = panel.hidden;

        aplicarEstado(abrir);

        if (abrir) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

    });

    // Comparar: la comparativa aún no existe; de momento solo
    // se avisa (ver aviso #compararAviso en tarifas.php).
    const btnComparar = document.getElementById('btnComparar');
    const aviso = document.getElementById('compararAviso');

    if (btnComparar && aviso) {

        btnComparar.addEventListener('click', () => {
            aviso.hidden = false;
        });

    }

}


/* =========================================================
   UNA REJILLA
   =========================================================
   Todo lo que sigue va dentro del <form> de una tabla, así
   las dos tablas de la página funcionan igual y sin
   mezclarse. Devuelve lo que necesita saber la página
   (si hay cambios sin guardar y si se está enviando).
========================================================= */

function inicializarRejillaTarifas(formulario) {

    const tbody = formulario.querySelector('tbody.tarifas-body');

    if (!tbody) {
        return null;
    }

    const plantilla = formulario.querySelector('template.plantilla-fila-tarifa');
    const estado = formulario.querySelector('.tarifas-estado');
    const btnGuardar = formulario.querySelector('button[type="submit"]');
    const buscador = formulario.querySelector('.tarifas-buscar');
    const esClientes = formulario.dataset.ambito === 'clientes';

    // Si venimos de un guardado con errores, lo que se ve aún
    // no está guardado aunque no se toque nada.
    const cambiosPendientesAlCargar = formulario.dataset.hayCambios === '1';

    let enviando = false;
    let contadorFilasNuevas = 0;


    /* =====================================================
       1. UTILIDADES
    ===================================================== */

    function filas() {
        return Array.from(tbody.querySelectorAll('tr.tarifa-fila'));
    }

    function filasVisibles() {
        return filas().filter(fila => !fila.hidden);
    }

    // Celdas de una fila por las que se mueve el teclado y se
    // reparte lo pegado: las de texto y los desplegables (IVA,
    // impuesto eléctrico), en el orden de las columnas.
    function celdasTexto(fila) {
        return Array.from(fila.querySelectorAll('input[type="text"], select[data-campo]'));
    }

    function esTexto(elemento) {
        return elemento instanceof HTMLInputElement && elemento.type === 'text';
    }

    // Pone un valor pegado en un desplegable si coincide con
    // alguna opción ("21", "21 %", "5,11" -> 5.11269632...).
    function ponerValorDesplegable(select, texto) {

        const numero = parseFloat(texto.replace(/[%\s]/g, '').replace(',', '.'));

        if (Number.isNaN(numero)) {
            return;
        }

        const opcion = Array.from(select.options).find(opcionActual =>
            Math.abs(parseFloat(opcionActual.value) - numero) < 0.01
        );

        if (opcion) {
            select.value = opcion.value;
        }

    }

    function valorCampo(input) {
        return input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
    }

    function camposEditables(raiz) {
        return raiz.querySelectorAll('input[data-campo], select[data-campo]');
    }

    // La primera fila de cada titular muestra su nombre; las
    // siguientes lo ocultan (como celdas combinadas en
    // Excel). Se recalcula al añadir/quitar.
    function actualizarPrimerasFilas() {

        let anterior = null;

        filas().forEach(fila => {
            fila.classList.toggle('tarifa-fila-extra', fila.dataset.titular === anterior);
            anterior = fila.dataset.titular;
        });

    }


    /* =====================================================
       2. CAMBIOS SIN GUARDAR
       =====================================================
       Cada campo recuerda su valor inicial; las celdas que
       difieren se resaltan y se cuentan junto al Guardar.
    ===================================================== */

    let hayCambios = cambiosPendientesAlCargar;

    function recordarValorInicial(raiz) {

        camposEditables(raiz).forEach(input => {
            input.dataset.original = valorCampo(input);
        });

    }

    function actualizarEstado() {

        let cambios = 0;

        camposEditables(tbody).forEach(input => {

            const cambiado = valorCampo(input) !== input.dataset.original;

            input.closest('td').classList.toggle('celda-cambiada', cambiado);

            if (cambiado) {
                cambios++;
            }

        });

        hayCambios = cambiosPendientesAlCargar || cambios > 0;

        if (!estado) {
            return;
        }

        if (cambios > 0) {
            estado.textContent = cambios === 1 ? '1 celda sin guardar' : `${cambios} celdas sin guardar`;
        } else {
            estado.textContent = cambiosPendientesAlCargar ? 'Hay errores sin guardar' : '';
        }

    }

    tbody.addEventListener('input', event => {

        const celda = event.target.closest('td');

        // Al corregir una celda marcada en rojo por el
        // servidor, se le quita la marca.
        if (celda && celda.classList.contains('celda-error')) {
            celda.classList.remove('celda-error');
            celda.removeAttribute('title');
        }

        actualizarEstado();

    });

    tbody.addEventListener('change', actualizarEstado);


    /* =====================================================
       3. MOVERSE CON EL TECLADO
       =====================================================
       Enter / flecha abajo -> misma columna, fila siguiente.
       Mayús+Enter / flecha arriba -> fila anterior.
       Tab funciona como siempre (celda siguiente).
       Enter nunca envía el formulario: se guarda solo con el
       botón.
    ===================================================== */

    function moverVertical(campo, salto) {

        const fila = campo.closest('tr');
        const columna = celdasTexto(fila).indexOf(campo);
        const todas = filasVisibles();
        const destino = todas[todas.indexOf(fila) + salto];

        if (!destino) {
            return;
        }

        const celdaDestino = celdasTexto(destino)[columna];

        if (celdaDestino) {
            celdaDestino.focus();
        }

    }

    tbody.addEventListener('keydown', event => {

        const campo = event.target;

        if (!(campo instanceof HTMLInputElement) && !(campo instanceof HTMLSelectElement)) {
            return;
        }

        if (event.key === 'Enter') {

            event.preventDefault();

            if (esTexto(campo) || campo instanceof HTMLSelectElement) {
                moverVertical(campo, event.shiftKey ? -1 : 1);
            }

        // En los desplegables las flechas cambian la opción,
        // como siempre; solo en las celdas de texto mueven.
        } else if (esTexto(campo) && event.key === 'ArrowDown') {

            event.preventDefault();
            moverVertical(campo, 1);

        } else if (esTexto(campo) && event.key === 'ArrowUp') {

            event.preventDefault();
            moverVertical(campo, -1);

        }

    });

    // Al entrar en una celda se selecciona su contenido, como
    // en Excel: escribir directamente sustituye el valor.
    tbody.addEventListener('focusin', event => {

        if (esTexto(event.target)) {
            event.target.select();
        }

    });


    /* =====================================================
       4. PEGAR DESDE EXCEL
       =====================================================
       Excel copia las celdas separadas por tabuladores y las
       filas por saltos de línea. Si lo pegado trae varias
       celdas, se reparten hacia la derecha y hacia abajo a
       partir de la celda actual (lo que no cabe se descarta).
    ===================================================== */

    tbody.addEventListener('paste', event => {

        const input = event.target;

        if (!esTexto(input)) {
            return;
        }

        const texto = (event.clipboardData || window.clipboardData).getData('text');

        if (!/[\t\n]/.test(texto.replace(/\r?\n$/, ''))) {
            return;
        }

        event.preventDefault();

        const bloque = texto
            .replace(/\r/g, '')
            .replace(/\n$/, '')
            .split('\n')
            .map(linea => linea.split('\t'));

        const filaInicio = input.closest('tr');
        const columnaInicio = celdasTexto(filaInicio).indexOf(input);
        const todas = filasVisibles();
        const indiceInicio = todas.indexOf(filaInicio);

        bloque.forEach((valores, desplazamientoFila) => {

            const fila = todas[indiceInicio + desplazamientoFila];

            if (!fila) {
                return;
            }

            const celdas = celdasTexto(fila);

            valores.forEach((valor, desplazamientoColumna) => {

                const celda = celdas[columnaInicio + desplazamientoColumna];

                if (celda instanceof HTMLSelectElement) {
                    ponerValorDesplegable(celda, valor);
                    celda.dispatchEvent(new Event('change', { bubbles: true }));
                } else if (celda) {
                    celda.value = valor.trim();
                    celda.dispatchEvent(new Event('input', { bubbles: true }));
                }

            });

        });

    });


    /* =====================================================
       5. AÑADIR / QUITAR FILAS
       =====================================================
       El titular de cada fila es un cliente o una
       comercializadora (data-titular).
    ===================================================== */

    // Crea una fila en blanco del mismo titular que
    // filaOrigen (copia su nombre y, si lo tiene, su NIF).
    function crearFilaNueva(filaOrigen) {

        contadorFilasNuevas++;

        const idTitular = String(Number(filaOrigen.dataset.titular));
        const clave = `n${idTitular}_${Date.now()}_${contadorFilasNuevas}`;

        const html = plantilla.innerHTML
            .replaceAll('__CLAVE__', clave)
            .replaceAll('__TITULAR__', idTitular);

        const contenedor = document.createElement('tbody');
        contenedor.innerHTML = html.trim();

        const fila = contenedor.querySelector('tr');
        const celdaTitular = fila.querySelector('.col-titular');
        const nombreTitular = nombreTitularDeFila(filaOrigen);
        const detalleOrigen = filaOrigen.querySelector('.detalle-titular');
        const botonNueva = fila.querySelector('[data-nueva-tarifa]');
        const textoAyuda = `Añadir otra tarifa a ${nombreTitular}`;

        celdaTitular.querySelector('.nombre-titular').textContent = nombreTitular;

        if (detalleOrigen) {
            celdaTitular.querySelector('.nombre-titular').after(detalleOrigen.cloneNode(true));
        }

        botonNueva.title = textoAyuda;
        botonNueva.setAttribute('aria-label', textoAyuda);

        // Una fila nueva cuenta como cambio solo cuando se
        // escribe algo en ella: su "valor inicial" es vacío
        // (o el valor por defecto, en casillas y desplegables).
        camposEditables(fila).forEach(inputFila => {
            inputFila.dataset.original = inputFila.type === 'text' ? '' : valorCampo(inputFila);
        });

        return fila;

    }

    function nombreTitularDeFila(fila) {
        return fila.querySelector('.nombre-titular').textContent.trim();
    }

    function ultimaFilaDe(idTitular) {
        const deEste = filas().filter(fila => fila.dataset.titular === idTitular);
        return deEste[deEste.length - 1];
    }

    // Quita la fila de la rejilla. Si era la única de su
    // titular, deja en su lugar una fila en blanco para poder
    // seguir rellenando.
    function quitarFila(fila) {

        const quedanOtras = filas().some(otra => otra !== fila && otra.dataset.titular === fila.dataset.titular);

        if (!quedanOtras) {
            fila.after(crearFilaNueva(fila));
        }

        fila.remove();
        actualizarPrimerasFilas();
        actualizarEstado();

    }

    function eliminarFila(fila) {

        // Fila aún no guardada: basta con quitarla.
        if (!fila.dataset.id) {
            quitarFila(fila);
            return;
        }

        const campoNombre = fila.querySelector('input[data-campo="nombre"]');
        const nombre = campoNombre.dataset.original || campoNombre.value;

        abrirModalEliminarTarifa(fila, `${nombreTitularDeFila(fila)} — ${nombre}`, esClientes, quitarFila);

    }

    tbody.addEventListener('click', event => {

        const botonNueva = event.target.closest('[data-nueva-tarifa]');

        if (botonNueva) {

            const filaOrigen = botonNueva.closest('tr');
            const nueva = crearFilaNueva(filaOrigen);

            ultimaFilaDe(filaOrigen.dataset.titular).after(nueva);
            actualizarPrimerasFilas();
            nueva.querySelector('input[data-campo="nombre"]').focus();

            return;

        }

        const botonEliminar = event.target.closest('[data-eliminar-fila]');

        if (botonEliminar) {
            eliminarFila(botonEliminar.closest('tr'));
        }

    });


    /* =====================================================
       6. BUSCAR
       =====================================================
       Oculta las filas cuyo titular (nombre o NIF) no
       contiene el texto buscado. Solo cambia lo que se ve:
       al guardar se envían todas las filas igualmente.
    ===================================================== */

    function normalizarBusqueda(texto) {
        return texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    }

    if (buscador) {

        buscador.addEventListener('input', () => {

            const buscado = normalizarBusqueda(buscador.value);

            filas().forEach(fila => {

                const texto = normalizarBusqueda(fila.querySelector('.col-titular').textContent);

                fila.hidden = buscado !== '' && !texto.includes(buscado);

            });

        });

        // Enter en el buscador no debe enviar el formulario.
        buscador.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

    }


    /* =====================================================
       7. GUARDAR
       =====================================================
       Toda la rejilla viaja en un único campo JSON
       (filas_json) para no chocar con el límite de campos
       por petición de PHP (max_input_vars); los campos
       sueltos se deshabilitan para que no se envíen también.
    ===================================================== */

    formulario.addEventListener('submit', event => {

        if (enviando) {
            event.preventDefault();
            return;
        }

        const datos = {};

        tbody.querySelectorAll('[name^="filas["]').forEach(input => {

            const partes = input.name.match(/^filas\[([^\]]+)\]\[([^\]]+)\]$/);

            if (!partes || (input.type === 'hidden' && partes[2] === 'activa')) {
                return;
            }

            const [, clave, campo] = partes;

            datos[clave] = datos[clave] || {};
            datos[clave][campo] = valorCampo(input);

        });

        let campoJson = formulario.querySelector('input[name="filas_json"]');

        if (!campoJson) {
            campoJson = document.createElement('input');
            campoJson.type = 'hidden';
            campoJson.name = 'filas_json';
            formulario.append(campoJson);
        }

        campoJson.value = JSON.stringify(datos);

        tbody.querySelectorAll('[name^="filas["]').forEach(input => {
            input.disabled = true;
        });

        enviando = true;

        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.textContent = 'Guardando…';
        }

    });

    // Si se vuelve a esta página con el botón Atrás del
    // navegador, puede restaurarse con los campos aún
    // deshabilitados del envío anterior.
    window.addEventListener('pageshow', event => {

        if (!event.persisted) {
            return;
        }

        tbody.querySelectorAll('[name^="filas["]').forEach(input => {
            input.disabled = false;
        });

        enviando = false;

        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.textContent = 'Guardar';
        }

    });


    /* =====================================================
       8. ARRANQUE
    ===================================================== */

    recordarValorInicial(tbody);
    actualizarEstado();

    return {
        hayCambios: () => hayCambios,
        enviando: () => enviando,
    };

}

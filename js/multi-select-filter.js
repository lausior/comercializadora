/* =========================================================
   FILTRO DESPLEGABLE DE SELECCIÓN MÚLTIPLE
   =========================================================
   Sustituye a los <select> de una sola opción en los
   filtros de columna de Clientes, Usuarios, Empresas y Logs.
   Cada filtro es un <div class="column-filter multi-select-filter">
   con una barra (.multi-select-input) que es a la vez el
   resumen de lo marcado (cerrada) y el buscador (al
   enfocarla, se vacía para poder escribir y filtrar las
   opciones del menú que se despliega debajo).

   Este archivo solo se encarga del COMPORTAMIENTO DEL
   WIDGET (abrir/cerrar, marcar casillas, buscar, actualizar
   la barra). La lógica de "qué filas ocultar según lo
   seleccionado" sigue viviendo en usuarios.js/clientes.js/
   empresas.js/logs.js, que leen la selección con
   window.obtenerSeleccionMultiFiltro(filtro).
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const filtrosMultiples = document.querySelectorAll('.multi-select-filter');


    // Sin acentos ni mayúsculas, para que "colon" encuentre
    // "Colón" igual que en las búsquedas del resto de la app.
    function normalizarBusqueda(texto) {

        return String(texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .trim();

    }


    // Escribe en la barra el resumen de lo marcado ("Ana
    // García", "3 seleccionados"...) o la deja vacía (para
    // que se vea el placeholder, p.ej. "Todos") si no hay
    // nada marcado. Se llama solo al cerrar el desplegable:
    // mientras está abierto, la barra se usa para escribir
    // la búsqueda y no debe pisarse con este resumen.
    function actualizarResumen(filtro) {

        const campoInput = filtro.querySelector('.multi-select-input');

        if (!campoInput) {
            return;
        }

        const seleccionados = Array.from(
            filtro.querySelectorAll('input[type="checkbox"]:checked')
        );

        if (seleccionados.length === 0) {
            campoInput.value = '';
        } else if (seleccionados.length === 1) {
            campoInput.value = seleccionados[0].value;
        } else {
            campoInput.value = seleccionados.length + ' seleccionados';
        }

    }


    // Marca en azul la fila de la opción (.multi-select-option)
    // según si su checkbox está marcado o no; así no depende de
    // la casilla (que va oculta) para mostrar la selección.
    function sincronizarOpcion(checkbox) {

        const opcion = checkbox.closest('.multi-select-option');

        if (opcion) {
            opcion.classList.toggle('is-checked', checkbox.checked);
        }

    }


    // Sube las opciones marcadas arriba del todo de la lista
    // (por delante de las que no lo están), para que si hay
    // muchas no haga falta buscar una ya marcada para
    // desmarcarla. Dentro de cada grupo se respeta el orden
    // original.
    function reordenarOpciones(filtro) {

        const lista = filtro.querySelector('.multi-select-options-list');

        if (!lista) {
            return;
        }

        const opciones = Array.from(
            lista.querySelectorAll('.multi-select-option')
        );

        const marcadas = opciones.filter(opcion => opcion.classList.contains('is-checked'));
        const sinMarcar = opciones.filter(opcion => !opcion.classList.contains('is-checked'));

        const referencia =
            lista.querySelector('.multi-select-no-results') ||
            lista.querySelector('.multi-select-clear-inline');

        [...marcadas, ...sinMarcar].forEach(opcion => {
            lista.insertBefore(opcion, referencia);
        });

    }


    // Muestra solo las opciones cuyo texto contiene la consulta
    // (sin acentos/mayúsculas) y el aviso de "Sin resultados."
    // si ninguna encaja. Con la consulta vacía, se ven todas.
    function filtrarOpciones(filtro, consultaTexto) {

        const consulta = normalizarBusqueda(consultaTexto);

        let algunaVisible = false;

        filtro.querySelectorAll('.multi-select-option').forEach(opcion => {

            const texto = normalizarBusqueda(
                opcion.querySelector('.multi-select-option-label')?.textContent || ''
            );

            const visible = texto.includes(consulta);

            opcion.hidden = !visible;

            if (visible) {
                algunaVisible = true;
            }

        });

        const sinResultados = filtro.querySelector('.multi-select-no-results');

        if (sinResultados) {
            sinResultados.hidden = algunaVisible;
        }

    }


    function cerrarTodosLosMenus() {

        document.querySelectorAll('.multi-select-filter').forEach(filtro => {

            const menu = filtro.querySelector('.multi-select-menu');
            const campoInput = filtro.querySelector('.multi-select-input');

            if (menu) {
                menu.hidden = true;
            }

            if (campoInput) {
                campoInput.setAttribute('aria-expanded', 'false');
            }

            // Al cerrar, la barra vuelve a mostrar el resumen de
            // lo marcado y se ven de nuevo todas las opciones,
            // para que la próxima vez que se abra empiece limpio.
            actualizarResumen(filtro);
            filtrarOpciones(filtro, '');

        });

    }


    /* =========================================================
       POSICIONAR EL MENÚ CON "position: fixed"
       =========================================================
       Los filtros de columna viven dentro de contenedores con
       scroll horizontal (.clientes-table-container /
       .usuarios-table-container, overflow-x: auto), lo que
       obliga al navegador a recortar también el eje vertical.
       Con "position: absolute" el menú quedaba cortado/oculto
       detrás de las filas de la tabla. Calculando la posición
       en píxeles respecto a la barra y usando "position: fixed"
       el menú escapa de ese recorte y flota por encima.
    ========================================================= */

    function posicionarMenu(barra, menu) {

        const rect = barra.getBoundingClientRect();

        menu.style.position = 'fixed';
        menu.style.top = (rect.bottom + 4) + 'px';
        menu.style.left = rect.left + 'px';
        menu.style.minWidth = rect.width + 'px';

        // Si se sale por la derecha del viewport, lo pegamos al
        // borde derecho en vez de dejarlo cortado.
        const desborde = menu.getBoundingClientRect().right - window.innerWidth;

        if (desborde > 0) {
            menu.style.left = (rect.left - desborde - 8) + 'px';
        }

    }


    filtrosMultiples.forEach(filtro => {

        const toggle = filtro.querySelector('.multi-select-toggle');
        const campoInput = filtro.querySelector('.multi-select-input');
        const menu = filtro.querySelector('.multi-select-menu');
        const botonLimpiar = filtro.querySelector('.multi-select-clear-inline');
        const checkboxes = filtro.querySelectorAll('input[type="checkbox"]');

        if (!toggle || !campoInput || !menu) {
            return;
        }

        function abrirMenu() {

            cerrarTodosLosMenus();

            // El orden (marcadas arriba) se recalcula aquí, al
            // abrir, y no en cada casilla marcada/desmarcada: si
            // se reordenara con el menú ya abierto, desmarcar
            // varias seguidas iría desplazando las siguientes
            // opciones bajo el cursor, haciendo que el siguiente
            // clic caiga sobre la opción equivocada (o sobre
            // ningún sitio) en vez de sobre la que tocaba
            // desmarcar a continuación.
            reordenarOpciones(filtro);

            menu.hidden = false;
            campoInput.setAttribute('aria-expanded', 'true');
            posicionarMenu(toggle, menu);

        }

        // Al enfocar la barra (con Tab, sin pasar por el
        // "mousedown" de abajo) se despliega el menú y se
        // vacía para poder escribir y buscar directamente; si
        // no se escribe nada, se ven todas las opciones para
        // elegir sin más.
        campoInput.addEventListener('focus', () => {

            abrirMenu();
            campoInput.value = '';
            filtrarOpciones(filtro, '');

        });

        // Pinchar en cualquier parte de la barra (el hueco de
        // alrededor, la flechita o el propio campo de texto)
        // abre el desplegable si estaba cerrado y lo cierra si
        // ya estaba abierto. Va en "mousedown" y no en "click"
        // porque, si el campo ya tenía el foco, el navegador no
        // vuelve a disparar "focus" al pincharlo otra vez: hay
        // que abrir/cerrar a mano comprobando el estado actual.
        toggle.addEventListener('mousedown', (event) => {

            if (document.activeElement !== campoInput) {
                campoInput.focus();
                return;
            }

            if (menu.hidden) {

                abrirMenu();
                campoInput.value = '';
                filtrarOpciones(filtro, '');

            } else {

                event.preventDefault();
                cerrarTodosLosMenus();
                campoInput.blur();

            }

        });

        campoInput.addEventListener('input', () => {
            filtrarOpciones(filtro, campoInput.value);
        });

        // Un click dentro del propio menú (sobre un checkbox o
        // su texto) no debe cerrarlo.
        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        checkboxes.forEach(checkbox => {

            // Estado inicial (por si alguna opción viniera
            // premarcada desde el HTML).
            sincronizarOpcion(checkbox);

            checkbox.addEventListener('change', () => {

                // No se reordena aquí a propósito (ver
                // abrirMenu()): con el menú ya abierto, el orden
                // se queda quieto mientras se marca/desmarca, así
                // el resto de opciones no se mueve bajo el cursor.
                sincronizarOpcion(checkbox);

                // Avisa a la página (usuarios.js/clientes.js/
                // logs.js) de que este filtro ha cambiado, con
                // el mismo evento "change" que ya escuchan.
                filtro.dispatchEvent(new Event('change'));

            });

        });

        // Cruz de cada opción marcada: la desmarca sin tener
        // que abrir el checkbox (que además va oculto).
        filtro.querySelectorAll('.multi-select-option-remove').forEach(botonQuitar => {

            botonQuitar.addEventListener('click', (event) => {

                event.stopPropagation();

                const opcion = botonQuitar.closest('.multi-select-option');
                const checkbox = opcion ? opcion.querySelector('input[type="checkbox"]') : null;

                if (!checkbox) {
                    return;
                }

                checkbox.checked = false;

                // Mismo motivo que en el "change" de la casilla:
                // no reordenar aquí, para no desplazar el resto
                // de opciones mientras el menú sigue abierto.
                sincronizarOpcion(checkbox);
                filtro.dispatchEvent(new Event('change'));

            });

        });

        if (botonLimpiar) {

            botonLimpiar.addEventListener('click', () => {

                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    sincronizarOpcion(checkbox);
                });

                reordenarOpciones(filtro);
                filtro.dispatchEvent(new Event('change'));

            });

        }

        // Por si alguna opción viniera premarcada desde el
        // HTML, la subimos arriba ya desde el principio.
        reordenarOpciones(filtro);

        actualizarResumen(filtro);

    });


    // Cerrar al pinchar fuera. Va en fase de "captura" (el
    // último parámetro "true") para que se ejecute ANTES que
    // cualquier otro listener de la página — incluidos los
    // botones "Editar"/"Borrar" de las tablas, que llaman a
    // event.stopPropagation() y, si este cierre estuviera en
    // la fase normal (burbuja), nunca le llegaría el click y el
    // desplegable se quedaría abierto ("fijo").
    document.addEventListener('click', (event) => {

        // Un click dentro del propio filtro (la barra o su
        // menú) lo gestiona cada uno por su cuenta más arriba.
        if (event.target instanceof Element && event.target.closest('.multi-select-filter')) {
            return;
        }

        cerrarTodosLosMenus();

    }, true);

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape') {
            cerrarTodosLosMenus();
        }

    });


    // Como el menú abierto queda fijado en píxeles ("position:
    // fixed"), si la página o el contenedor con scroll
    // horizontal de la tabla se desplazan, el menú se quedaría
    // "flotando" lejos de su barra. Más simple que reposicionarlo
    // en cada scroll: lo cerramos. (Se usa "capture" porque el
    // scroll de un contenedor interno, como la tabla, no hace
    // bubbling). El scroll interno del propio menú de checkboxes
    // no debe cerrarlo.
    window.addEventListener('scroll', (event) => {

        if (event.target instanceof Element && event.target.closest('.multi-select-menu')) {
            return;
        }

        cerrarTodosLosMenus();

    }, true);

    window.addEventListener('resize', cerrarTodosLosMenus);


    /* =========================================================
       API usada por usuarios.js / clientes.js / empresas.js /
       logs.js
    ========================================================= */

    window.obtenerSeleccionMultiFiltro = function (filtro) {

        return Array.from(
            filtro.querySelectorAll('input[type="checkbox"]:checked')
        ).map(checkbox => checkbox.value);

    };

    window.limpiarMultiFiltro = function (filtro) {

        filtro.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
            sincronizarOpcion(checkbox);
        });

        reordenarOpciones(filtro);
        actualizarResumen(filtro);

    };


    // Marca las casillas cuyo valor esté en "valores" (y desmarca
    // el resto) y refresca el aspecto del filtro igual que si se
    // hubieran marcado a mano. La usan usuarios.js/empresas.js
    // para restaurar los filtros guardados al volver de
    // activar/desactivar una fila.
    window.marcarSeleccionMultiFiltro = function (filtro, valores) {

        const seleccion = Array.isArray(valores) ? valores : [];

        filtro.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = seleccion.includes(checkbox.value);
            sincronizarOpcion(checkbox);
        });

        reordenarOpciones(filtro);
        actualizarResumen(filtro);

    };


    /* =========================================================
       RECORDAR FILTROS ENTRE RECARGAS
       =========================================================
       Mismo comportamiento que usuarios.js/empresas.js/
       comercializadoras.js: los filtros marcados se guardan en
       sessionStorage (solo duran lo que la pestaña) para que
       no se pierdan al volver al listado tras editar, eliminar,
       activar/desactivar o cambiar de mes.

       Uso:
         const memoria = window.crearMemoriaFiltros('filtrosX', filtros);
         memoria.restaurar();   // al cargar, antes de pintar
         memoria.guardar();     // en cada cambio de filtro
         memoria.olvidar();     // en "Limpiar filtros"

       "filtros" pueden ser filtros múltiples (.multi-select-filter)
       o <input>/<select> normales. Se identifican por su
       data-column (los de escritorio y móvil de una misma
       columna comparten valor) o, si no tienen, por su id.
    ========================================================= */

    window.crearMemoriaFiltros = function (clave, filtros) {

        const lista = Array.from(filtros);

        const claveFiltro = filtro => filtro.dataset.column !== undefined
            ? 'columna-' + filtro.dataset.column
            : filtro.id;

        const esMultiple = filtro => filtro.classList.contains('multi-select-filter');

        return {

            guardar() {

                const datos = {};

                lista.forEach(filtro => {

                    const valor = esMultiple(filtro)
                        ? window.obtenerSeleccionMultiFiltro(filtro)
                        : filtro.value;

                    const vacio = Array.isArray(valor) ? valor.length === 0 : valor === '';
                    const id = claveFiltro(filtro);

                    // Escritorio y móvil comparten clave; el vacío
                    // de uno no debe pisar el valor del otro.
                    if (!vacio || datos[id] === undefined) {
                        datos[id] = valor;
                    }

                });

                try {
                    sessionStorage.setItem(clave, JSON.stringify(datos));
                } catch (error) {
                    // Sin almacenamiento (modo privado...): no se recuerdan.
                }

            },

            restaurar() {

                let datos = null;

                try {
                    datos = JSON.parse(sessionStorage.getItem(clave));
                } catch (error) {
                    datos = null;
                }

                if (!datos) {
                    return;
                }

                lista.forEach(filtro => {

                    const id = claveFiltro(filtro);

                    if (!(id in datos)) {
                        return;
                    }

                    if (esMultiple(filtro)) {
                        window.marcarSeleccionMultiFiltro(filtro, datos[id]);
                    } else if (typeof datos[id] === 'string') {
                        filtro.value = datos[id];
                    }

                });

            },

            olvidar() {

                try {
                    sessionStorage.removeItem(clave);
                } catch (error) {
                    // Nada que limpiar.
                }

            }

        };

    };

});

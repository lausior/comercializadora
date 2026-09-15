/* =========================================================
   FILTRO DESPLEGABLE DE SELECCIÓN MÚLTIPLE
   =========================================================
   Sustituye a los <select> de una sola opción en los
   filtros de columna de Clientes, Usuarios y Logs. Cada
   filtro es un <div class="column-filter multi-select-filter">
   con un botón que abre un menú de checkboxes.

   Este archivo solo se encarga del COMPORTAMIENTO DEL
   WIDGET (abrir/cerrar, marcar casillas, actualizar la
   etiqueta del botón). La lógica de "qué filas ocultar según
   lo seleccionado" sigue viviendo en usuarios.js/clientes.js/
   logs.js, que leen la selección con
   window.obtenerSeleccionMultiFiltro(filtro).
========================================================= */

document.addEventListener('DOMContentLoaded', () => {

    const filtrosMultiples = document.querySelectorAll('.multi-select-filter');


    function actualizarEtiqueta(filtro) {

        const etiqueta = filtro.querySelector('.multi-select-toggle-label');

        if (!etiqueta) {
            return;
        }

        const seleccionados = Array.from(
            filtro.querySelectorAll('input[type="checkbox"]:checked')
        );

        if (seleccionados.length === 0) {
            etiqueta.textContent = filtro.dataset.placeholder || 'Todos';
            return;
        }

        if (seleccionados.length === 1) {
            etiqueta.textContent = seleccionados[0].value;
            return;
        }

        etiqueta.textContent = seleccionados.length + ' seleccionados';

    }


    function cerrarTodosLosMenus() {

        document.querySelectorAll('.multi-select-filter .multi-select-menu').forEach(menu => {
            menu.hidden = true;
        });

        document.querySelectorAll('.multi-select-filter .multi-select-toggle').forEach(boton => {
            boton.setAttribute('aria-expanded', 'false');
        });

    }


    filtrosMultiples.forEach(filtro => {

        const boton = filtro.querySelector('.multi-select-toggle');
        const menu = filtro.querySelector('.multi-select-menu');
        const botonLimpiar = filtro.querySelector('.multi-select-clear-inline');
        const checkboxes = filtro.querySelectorAll('input[type="checkbox"]');

        if (!boton || !menu) {
            return;
        }

        boton.addEventListener('click', (event) => {

            event.stopPropagation();

            const estaAbierto = !menu.hidden;

            cerrarTodosLosMenus();

            if (!estaAbierto) {
                menu.hidden = false;
                boton.setAttribute('aria-expanded', 'true');
            }

        });

        // Un click dentro del propio menú (sobre un checkbox o
        // su texto) no debe cerrarlo.
        menu.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        checkboxes.forEach(checkbox => {

            checkbox.addEventListener('change', () => {

                actualizarEtiqueta(filtro);

                // Avisa a la página (usuarios.js/clientes.js/
                // logs.js) de que este filtro ha cambiado, con
                // el mismo evento "change" que ya escuchan.
                filtro.dispatchEvent(new Event('change'));

            });

        });

        if (botonLimpiar) {

            botonLimpiar.addEventListener('click', () => {

                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                actualizarEtiqueta(filtro);
                filtro.dispatchEvent(new Event('change'));

            });

        }

        actualizarEtiqueta(filtro);

    });


    document.addEventListener('click', cerrarTodosLosMenus);

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape') {
            cerrarTodosLosMenus();
        }

    });


    /* =========================================================
       API usada por usuarios.js / clientes.js / logs.js
    ========================================================= */

    window.obtenerSeleccionMultiFiltro = function (filtro) {

        return Array.from(
            filtro.querySelectorAll('input[type="checkbox"]:checked')
        ).map(checkbox => checkbox.value);

    };

    window.limpiarMultiFiltro = function (filtro) {

        filtro.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        actualizarEtiqueta(filtro);

    };

});

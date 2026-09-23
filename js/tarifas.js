document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       SELECCIÓN DE COMERCIALIZADORA + SERVICIO (LUZ / GAS)
       =========================================================
       El select de servicio depende de lo que suministre la
       comercializadora elegida (cada <option> de comercializadora
       lleva data-luz/data-gas, ver tarifas.php): si solo
       suministra luz, aquí solo se puede elegir "Luz", y así con
       gas o con las dos.
    ========================================================= */

    const selectComercializadora = document.getElementById('id_comercializadora');
    const selectServicio = document.getElementById('tipo_suministro');
    const btnContinuar = document.getElementById('btnContinuarTarifa');

    if (!selectComercializadora || !selectServicio) {
        return;
    }

    function actualizarOpcionesServicio() {

        const opcionElegida = selectComercializadora.selectedOptions[0];

        selectServicio.innerHTML = '';

        if (!opcionElegida || opcionElegida.value === '') {

            selectServicio.disabled = true;
            selectServicio.appendChild(new Option('Selecciona antes una comercializadora', ''));

            if (btnContinuar) {
                btnContinuar.disabled = true;
            }

            return;

        }

        const opciones = [];

        if (opcionElegida.dataset.luz === '1') {
            opciones.push(['luz', '⚡ Luz']);
        }

        if (opcionElegida.dataset.gas === '1') {
            opciones.push(['gas', '🔥 Gas']);
        }

        selectServicio.appendChild(new Option('Selecciona un servicio', ''));

        opciones.forEach(([valor, etiqueta]) => {
            selectServicio.appendChild(new Option(etiqueta, valor));
        });

        selectServicio.disabled = false;

        // Si solo suministra un servicio, no tiene sentido
        // obligar a elegirlo a mano: se preselecciona y ya
        // está listo para continuar.
        if (opciones.length === 1) {
            selectServicio.value = opciones[0][0];
        }

        if (btnContinuar) {
            btnContinuar.disabled = selectServicio.value === '';
        }

    }

    selectComercializadora.addEventListener('change', actualizarOpcionesServicio);

    selectServicio.addEventListener('change', () => {

        if (btnContinuar) {
            btnContinuar.disabled = selectServicio.value === '';
        }

    });

    actualizarOpcionesServicio();

});

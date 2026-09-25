document.addEventListener('DOMContentLoaded', function () {

    const resizer = document.getElementById('sidebarResizer');

    // Mismo punto de corte que el @media (max-width: 680px)
    // del CSS. Por debajo de aquí, el sidebar va siempre
    // comprimido y el botón queda desactivado.
    const MOBILE_BREAKPOINT = 680;

    function esMovil() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }

    function estaGuardadoComoComprimido() {
        return localStorage.getItem('sidebarCollapsed') === 'true';
    }


    /*
     * Aplicar el estado correcto según el tamaño de pantalla.
     * El estado inicial ya lo aplica el script síncrono de
     * templates/header.php (antes de pintar la página, para
     * evitar el salto visual). Aquí solo hace falta reaccionar
     * si la ventana cambia de tamaño en caliente.
     */

    function aplicarEstadoSegunTamano() {

        if (esMovil()) {
            const colapsado = localStorage.getItem('sidebarCollapsed');
            document.body.classList.toggle(
                'sidebar-collapsed',
                colapsado === null ? true : colapsado === 'true'
            );
            return;
        }

        document.body.classList.toggle(
            'sidebar-collapsed',
            estaGuardadoComoComprimido()
        );

    }

    let anchoMovilAnterior = esMovil();

    window.addEventListener('resize', function () {

        const esMovilAhora = esMovil();

        if (esMovilAhora !== anchoMovilAnterior) {
            anchoMovilAnterior = esMovilAhora;
            aplicarEstadoSegunTamano();
        }

    });


    /*
     * Botón de comprimir / expandir
     */

    if (!resizer) {
        return;
    }

    resizer.addEventListener('click', function () {

        const comprimido = document.body.classList.toggle('sidebar-collapsed');

        localStorage.setItem('sidebarCollapsed', comprimido ? 'true' : 'false');

    });

});


/*
 * Submenú de Ayuda (Acerca de / Manual de usuario)
 */

document.addEventListener('DOMContentLoaded', function () {
    const ayudaToggle = document.getElementById('ayuda-toggle');
    const ayudaSubmenu = document.getElementById('ayuda-submenu');

    if (ayudaToggle && ayudaSubmenu) {
        const actualizarEstadoAyuda = function () {
            const abierto = ayudaSubmenu.classList.contains('open');
            ayudaToggle.classList.toggle('open', abierto);
            ayudaToggle.setAttribute('aria-expanded', String(abierto));
        };

        ayudaToggle.addEventListener('click', function () {
            ayudaSubmenu.classList.toggle('open');
            actualizarEstadoAyuda();
        });

        actualizarEstadoAyuda();
    }
});

document.addEventListener('DOMContentLoaded', function () {

        const sidebar = document.getElementById('sidebar');
        const resizer = document.getElementById('sidebarResizer');

        if (!sidebar || !resizer) {
            return;
        }


        /*
         * CONFIGURACIÓN
         */

        const MIN_WIDTH = 64;
        const MAX_WIDTH = 350;
        const DEFAULT_WIDTH = 236;

        // Mismo punto de corte que el @media (max-width: 680px)
        // del CSS. Por debajo de aquí, el ancho lo decide el
        // CSS (comprimido a iconos), no el ancho guardado en
        // localStorage de una sesión de escritorio.
        const MOBILE_BREAKPOINT = 680;

        function esMovil() {
            return window.innerWidth <= MOBILE_BREAKPOINT;
        }


        /*
         * Aplicar el ancho correcto según el tamaño de pantalla
         */

        function aplicarAnchoInicial() {

            if (esMovil()) {

                // En móvil no forzamos ningún ancho por JS:
                // quitamos cualquier valor puesto a mano para
                // que mande la regla del CSS (64px, iconos
                // solos) y marcamos el sidebar como comprimido.

                document.documentElement.style.removeProperty(
                    '--sidebar-width'
                );

                document.body.classList.add('sidebar-collapsed');

                return;

            }


            /*
             * Recuperar ancho guardado (solo tiene sentido en
             * escritorio, donde sí se puede arrastrar).
             */

            const savedWidth =
                parseInt(localStorage.getItem('sidebarWidth'), 10);


            if (
                !isNaN(savedWidth) &&
                savedWidth >= MIN_WIDTH &&
                savedWidth <= MAX_WIDTH
            ) {

                setSidebarWidth(savedWidth);

            } else {

                setSidebarWidth(DEFAULT_WIDTH);

            }

        }

        aplicarAnchoInicial();


        /*
         * Si la ventana pasa de escritorio a móvil (o al
         * revés) sin recargar la página -redimensionando el
         * navegador, girando el móvil-, se vuelve a aplicar.
         */

        let anchoMovilAnterior = esMovil();

        window.addEventListener('resize', function () {

            const esMovilAhora = esMovil();

            if (esMovilAhora !== anchoMovilAnterior) {

                anchoMovilAnterior = esMovilAhora;

                aplicarAnchoInicial();

            }

        });


        /*
         * Cambiar cursor mientras se arrastra
         */

        resizer.addEventListener('mousedown', function (event) {

            event.preventDefault();

            document.body.classList.add('sidebar-resizing');

            document.addEventListener(
                'mousemove',
                resizeSidebar
            );

            document.addEventListener(
                'mouseup',
                stopResize
            );

        });


        /*
         * Cambiar ancho
         */

        function resizeSidebar(event) {

            let newWidth = event.clientX;

            if (newWidth < MIN_WIDTH) {
                newWidth = MIN_WIDTH;
            }

            if (newWidth > MAX_WIDTH) {
                newWidth = MAX_WIDTH;
            }

            setSidebarWidth(newWidth);

        }


        /*
         * Finalizar arrastre
         */

        function stopResize() {

            document.body.classList.remove(
                'sidebar-resizing'
            );

            document.removeEventListener(
                'mousemove',
                resizeSidebar
            );

            document.removeEventListener(
                'mouseup',
                stopResize
            );


            /*
             * Guardar ancho
             */

            const currentWidth =
                parseInt(
                    getComputedStyle(document.documentElement)
                        .getPropertyValue('--sidebar-width'),
                    10
                );


            localStorage.setItem(
                'sidebarWidth',
                currentWidth
            );

        }


        /*
         * Aplicar ancho
         */

        function setSidebarWidth(width) {

            document.documentElement.style.setProperty(
                '--sidebar-width',
                width + 'px'
            );


            /*
             * Mostrar / ocultar texto
             */

            if (width <= MIN_WIDTH) {

                document.body.classList.add(
                    'sidebar-collapsed'
                );

            } else {

                document.body.classList.remove(
                    'sidebar-collapsed'
                );

            }

        }

    });


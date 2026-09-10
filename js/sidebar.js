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


        /*
         * Recuperar ancho guardado
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


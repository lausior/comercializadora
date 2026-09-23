document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       MODAL "SEGURIDAD" (CAMBIAR CONTRASEÑA)
       =========================================================
       El botón "Seguridad" del sidebar ya no lleva a una página
       propia: abre este modal, disponible desde cualquier
       pantalla (el modal vive en templates/sidebar.php). Mismo
       patrón que el modal "Acerca de" (ver js/acerca.js).
    ========================================================= */

    const abrirSeguridad = document.getElementById('abrirModalSeguridad');
    const cerrarSeguridad = document.getElementById('cerrarModalSeguridad');
    const modalSeguridad = document.getElementById('modalSeguridad');

    if (abrirSeguridad && cerrarSeguridad && modalSeguridad) {

        function cerrarModalSeguridad() {
            modalSeguridad.style.display = 'none';
            document.body.classList.remove('modal-abierto');
        }

        abrirSeguridad.addEventListener('click', () => {
            modalSeguridad.style.display = 'flex';
            document.body.classList.add('modal-abierto');
            document.getElementById('password_nueva')?.focus();
        });

        cerrarSeguridad.addEventListener('click', cerrarModalSeguridad);

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && modalSeguridad.style.display !== 'none') {
                cerrarModalSeguridad();
            }
        });

        // Si el modal ya viene abierto de servidor (volvemos de un
        // envío anterior con error o con éxito, ver sidebar.php),
        // se marca la sesión como "hay un modal abierto" igual que
        // si se hubiera pulsado el botón, para que el resto de la
        // interfaz (scroll, etc.) se comporte igual.
        if (modalSeguridad.style.display !== 'none') {
            document.body.classList.add('modal-abierto');
        }

    }


    /* =========================================================
       CAMBIAR CONTRASEÑA
    ========================================================= */

    const passwordNueva = document.getElementById('password_nueva');
    const passwordConfirmar = document.getElementById('password_confirmar');
    const listaRequisitos = document.getElementById('passwordRequisitos');
    const mensajeError = document.getElementById('form-error-general');
    const mensajeExito = document.getElementById('form-success-general');
    const formPassword = passwordNueva ? passwordNueva.closest('form') : null;


    /* MOSTRAR / OCULTAR CONTRASEÑA */

    document.querySelectorAll('.password-toggle').forEach(boton => {

        const input = document.getElementById(boton.dataset.target);

        if (!input) {
            return;
        }

        boton.addEventListener('click', () => {

            if (input.type === 'password') {
                input.type = 'text';
                boton.innerHTML = '<i class="bi bi-eye"></i>';
            } else {
                input.type = 'password';
                boton.innerHTML = '<i class="bi bi-eye-slash"></i>';
            }

        });

    });


    if (formPassword && passwordConfirmar && listaRequisitos) {

        const REQUISITOS = {
            longitud: valor => valor.length >= 8,
            mayuscula: valor => /[A-Z]/.test(valor),
            minuscula: valor => /[a-z]/.test(valor),
            numero: valor => /[0-9]/.test(valor),
            especial: valor => /[^A-Za-z0-9]/.test(valor),
            coinciden: valor => valor !== '' && valor === passwordConfirmar.value
        };

        const itemsRequisitos = listaRequisitos.querySelectorAll('[data-req]');

        function actualizarRequisitos() {

            const valor = passwordNueva.value;

            let todosCumplidos = true;

            itemsRequisitos.forEach(item => {

                const requisito = item.dataset.req;
                const cumple = REQUISITOS[requisito](valor);

                item.classList.toggle('cumplido', cumple);

                if (!cumple) {
                    todosCumplidos = false;
                }

            });

            return todosCumplidos;

        }

        function mostrarError(texto) {

            if (mensajeExito) {
                mensajeExito.style.display = 'none';
            }

            if (!mensajeError) {
                return;
            }

            mensajeError.textContent = texto;
            mensajeError.style.display = 'block';

        }

        function ocultarError() {

            if (!mensajeError) {
                return;
            }

            mensajeError.textContent = '';
            mensajeError.style.display = 'none';

        }

        passwordNueva.addEventListener('input', () => {
            actualizarRequisitos();
            ocultarError();
        });

        passwordConfirmar.addEventListener('input', () => {

            actualizarRequisitos();
            ocultarError();

        });

        formPassword.addEventListener('submit', event => {

            const requisitosCumplidos = actualizarRequisitos();

            if (!requisitosCumplidos) {

                event.preventDefault();

                mostrarError(
                    'La contraseña nueva no cumple todos los requisitos marcados en la lista.'
                );

                passwordNueva.focus();

            }

        });

        actualizarRequisitos();

    }

});

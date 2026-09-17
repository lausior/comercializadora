document.addEventListener('DOMContentLoaded', () => {

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
            especial: valor => /[^A-Za-z0-9]/.test(valor)
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

        passwordConfirmar.addEventListener('input', ocultarError);

        formPassword.addEventListener('submit', event => {

            const requisitosCumplidos = actualizarRequisitos();

            if (!requisitosCumplidos) {

                event.preventDefault();

                mostrarError(
                    'La contraseña nueva no cumple todos los requisitos marcados en la lista.'
                );

                passwordNueva.focus();

                return;

            }

            if (passwordNueva.value !== passwordConfirmar.value) {

                event.preventDefault();

                mostrarError('Las dos contraseñas nuevas no coinciden.');

                passwordConfirmar.focus();

            }

        });

        actualizarRequisitos();

    }

});

document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       ELEMENTOS
    ========================================================= */

    const form = document.querySelector('.login-form');
    const passwordNueva = document.getElementById('password_nueva');
    const passwordConfirmar = document.getElementById('password_confirmar');
    const listaRequisitos = document.getElementById('passwordRequisitos');
    const mensajeGeneral = document.getElementById('form-error-general');

    if (!form || !passwordNueva || !passwordConfirmar || !listaRequisitos) {
        return;
    }


    /* =========================================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================================= */

    document.querySelectorAll('.password-toggle').forEach(boton => {

        const input = document.getElementById(boton.dataset.target);

        if (!input) {
            return;
        }

        boton.addEventListener('click', () => {

            if (input.type === 'password') {
                input.type = 'text';
                boton.textContent = '🙈';
            } else {
                input.type = 'password';
                boton.textContent = '👁';
            }

        });

    });


    /* =========================================================
       REQUISITOS
       Cada uno es una función que dice si el valor actual
       de la contraseña nueva lo cumple.
    ========================================================= */

    const REQUISITOS = {
        longitud: valor => valor.length >= 8,
        mayuscula: valor => /[A-Z]/.test(valor),
        minuscula: valor => /[a-z]/.test(valor),
        numero: valor => /[0-9]/.test(valor),
        especial: valor => /[^A-Za-z0-9]/.test(valor)
    };

    const itemsRequisitos = listaRequisitos.querySelectorAll('[data-req]');


    /* =========================================================
       MARCAR REQUISITOS CUMPLIDOS
    ========================================================= */

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


    /* =========================================================
       MENSAJE GENERAL
    ========================================================= */

    function mostrarMensaje(texto) {

        if (!mensajeGeneral) {
            return;
        }

        mensajeGeneral.textContent = texto;
        mensajeGeneral.style.display = 'block';

    }

    function ocultarMensaje() {

        if (!mensajeGeneral) {
            return;
        }

        mensajeGeneral.textContent = '';
        mensajeGeneral.style.display = 'none';

    }


    /* =========================================================
       EVENTOS
    ========================================================= */

    passwordNueva.addEventListener('input', () => {

        actualizarRequisitos();
        ocultarMensaje();

    });

    passwordConfirmar.addEventListener('input', ocultarMensaje);


    form.addEventListener('submit', event => {

        const requisitosCumplidos = actualizarRequisitos();

        if (!requisitosCumplidos) {

            event.preventDefault();

            mostrarMensaje(
                'La contraseña nueva no cumple todos los requisitos marcados en la lista.'
            );

            passwordNueva.focus();

            return;

        }

        if (passwordNueva.value !== passwordConfirmar.value) {

            event.preventDefault();

            mostrarMensaje('Las dos contraseñas nuevas no coinciden.');

            passwordConfirmar.focus();

        }

    });


    /* =========================================================
       ESTADO INICIAL
       (por si el navegador rellena el campo automáticamente)
    ========================================================= */

    actualizarRequisitos();

});

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
                boton.innerHTML = '<i class="bi bi-eye"></i>';
            } else {
                input.type = 'password';
                boton.innerHTML = '<i class="bi bi-eye-slash"></i>';
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
        especial: valor => /[^A-Za-z0-9]/.test(valor),
        coinciden: valor => valor !== '' && valor === passwordConfirmar.value
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

    /* -----------------------------------------------
       ERROR DE CADA CAMPO
       Al salir del campo (blur) se marca en rojo con su
       mensaje debajo; al enviar, se marcan los dos.
    ------------------------------------------------ */

    const VALIDADORES = new Map([
        [passwordNueva, valor => {
            if (valor === '') return 'Este campo es obligatorio.';
            const cumpleTodos = Object.keys(REQUISITOS)
                .filter(requisito => requisito !== 'coinciden')
                .every(requisito => REQUISITOS[requisito](valor));
            return cumpleTodos ? null : 'La contraseña no cumple todos los requisitos de la lista.';
        }],
        [passwordConfirmar, valor => {
            if (valor === '') return 'Este campo es obligatorio.';
            return valor === passwordNueva.value ? null : 'Las contraseñas no coinciden.';
        }]
    ]);

    function validarCampo(input) {

        const error = VALIDADORES.get(input)(input.value);
        const envoltorio = input.closest('.password-wrapper') || input;
        let contenedor = envoltorio.nextElementSibling;

        if (!contenedor || !contenedor.classList.contains('login-field-error')) {
            contenedor = null;
        }

        input.classList.toggle('input-error', Boolean(error));

        if (error && !contenedor) {
            contenedor = document.createElement('small');
            contenedor.className = 'login-field-error';
            envoltorio.insertAdjacentElement('afterend', contenedor);
        }

        if (contenedor) {
            if (error) {
                contenedor.textContent = error;
            } else {
                contenedor.remove();
            }
        }

        return !error;

    }

    VALIDADORES.forEach((validador, input) => {

        input.addEventListener('blur', () => validarCampo(input));

        input.addEventListener('input', () => {

            actualizarRequisitos();

            // Mientras se escribe solo se QUITAN errores; la
            // confirmación depende también de la nueva.
            [passwordNueva, passwordConfirmar].forEach(campo => {
                if (campo.classList.contains('input-error') && VALIDADORES.get(campo)(campo.value) === null) {
                    validarCampo(campo);
                }
            });

            if (!passwordNueva.classList.contains('input-error') && !passwordConfirmar.classList.contains('input-error')) {
                ocultarMensaje();
            }

        });

    });


    form.addEventListener('submit', event => {

        actualizarRequisitos();

        const nuevaValida = validarCampo(passwordNueva);
        const confirmarValida = validarCampo(passwordConfirmar);

        if (!nuevaValida || !confirmarValida) {

            event.preventDefault();

            mostrarMensaje('El formulario contiene errores. Revísalos antes de enviarlo.');

            (nuevaValida ? passwordConfirmar : passwordNueva).focus();

        }

    });


    /* =========================================================
       ESTADO INICIAL
       (por si el navegador rellena el campo automáticamente)
    ========================================================= */

    actualizarRequisitos();

});

document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       ELEMENTOS
    ========================================================== */

    const form = document.querySelector('.login-form');
    const usuario = document.getElementById('usuario');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');


    /* =========================================================
       COMPROBAR ELEMENTOS
    ========================================================== */

    if (!form || !usuario || !password) {
        console.error('No se ha encontrado el formulario de login.');
        return;
    }


    /* =========================================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================================== */

    if (togglePassword) {

        togglePassword.addEventListener('click', () => {

            if (password.type === 'password') {

                password.type = 'text';
                togglePassword.textContent = '🙈';

            } else {

                password.type = 'password';
                togglePassword.textContent = '👁';

            }

        });

    }


    /* =========================================================
       CREAR ERROR
    ========================================================== */

    function mostrarError(campo, mensaje) {

        eliminarError(campo);

        campo.classList.add('input-error');

        const error = document.createElement('small');

        error.className = 'login-field-error';
        error.textContent = mensaje;

        campo.parentElement.appendChild(error);
    }


    /* =========================================================
       ELIMINAR ERROR
    ========================================================== */

    function eliminarError(campo) {

        campo.classList.remove('input-error');

        const error =
            campo.parentElement.querySelector('.login-field-error');

        if (error) {
            error.remove();
        }

    }


    /* =========================================================
       VALIDAR USUARIO
    ========================================================== */

    function validarUsuario() {

        const valor = usuario.value.trim();

        eliminarError(usuario);


        if (valor === '') {

            mostrarError(
                usuario,
                'Introduce tu usuario o correo electrónico.'
            );

            return false;
        }


        if (valor.length < 3) {

            mostrarError(
                usuario,
                'El usuario debe tener al menos 3 caracteres.'
            );

            return false;
        }


        if (valor.length > 100) {

            mostrarError(
                usuario,
                'El usuario o correo electrónico es demasiado largo.'
            );

            return false;
        }


        /* Si contiene @, comprobamos correo */

        if (valor.includes('@')) {

            const emailRegex =
                /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

            if (!emailRegex.test(valor)) {

                mostrarError(
                    usuario,
                    'Introduce un correo electrónico válido.'
                );

                return false;
            }

        } else {

            /* Comprobar nombre de usuario */

            const usuarioRegex =
                /^[a-zA-Z0-9._-]+$/;

            if (!usuarioRegex.test(valor)) {

                mostrarError(
                    usuario,
                    'El usuario contiene caracteres no válidos.'
                );

                return false;
            }

        }

        return true;
    }


    /* =========================================================
       VALIDAR CONTRASEÑA
    ========================================================== */

    function validarPassword() {

        const valor = password.value;

        eliminarError(password);


        if (valor === '') {

            mostrarError(
                password,
                'Introduce tu contraseña.'
            );

            return false;
        }


        if (valor.length < 8) {

            mostrarError(
                password,
                'La contraseña debe tener al menos 8 caracteres.'
            );

            return false;
        }


        if (valor.length > 128) {

            mostrarError(
                password,
                'La contraseña no puede superar los 128 caracteres.'
            );

            return false;
        }


        return true;
    }


    /* =========================================================
       SUBMIT DEL FORMULARIO
    ========================================================== */

    form.addEventListener('submit', (event) => {

        event.preventDefault();


        const usuarioCorrecto =
            validarUsuario();

        const passwordCorrecta =
            validarPassword();


        /* =====================================================
           HAY ERRORES
        ====================================================== */

        if (!usuarioCorrecto || !passwordCorrecta) {

            if (!usuarioCorrecto) {
                usuario.focus();
            } else {
                password.focus();
            }

            return;
        }


        /* =====================================================
           VALIDACIÓN CORRECTA
        ====================================================== */

        console.log('Validación correcta. Enviando formulario...');

        /*
         * Enviamos el formulario directamente respetando:
         * action="index.php"
         * method="POST"
         */
        HTMLFormElement.prototype.submit.call(form);

    });


    /* =========================================================
       VALIDACIÓN AL ESCRIBIR
    ========================================================== */

    usuario.addEventListener('input', () => {

        if (usuario.value.trim() !== '') {
            eliminarError(usuario);
        }

    });


    password.addEventListener('input', () => {

        if (password.value !== '') {
            eliminarError(password);
        }

    });


    /* =========================================================
       VALIDACIÓN AL SALIR DEL CAMPO
    ========================================================== */

    usuario.addEventListener('blur', validarUsuario);

    password.addEventListener('blur', validarPassword);

});


document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       ELEMENTOS
    ========================================================== */

    const form = document.getElementById('signinForm');

    const nombre = document.getElementById('nombre');
    const apellidos = document.getElementById('apellidos');
    const usuario = document.getElementById('usuario');
    const email = document.getElementById('email');

    const password = document.getElementById('password');
    const passwordConfirm =
        document.getElementById('password_confirm');

    const terminos =
        document.getElementById('terminos');

    const togglePassword =
        document.getElementById('togglePassword');

    const togglePasswordConfirm =
        document.getElementById('togglePasswordConfirm');

    const generalError =
        document.getElementById('signinError');


    /* =========================================================
       COMPROBAR FORMULARIO
    ========================================================== */

    if (!form) {

        console.error(
            'No se ha encontrado el formulario de registro.'
        );

        return;
    }


    /* =========================================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================================== */

    function configurarTogglePassword(boton, campo) {

        if (!boton || !campo) {
            return;
        }

        boton.addEventListener('click', () => {

            const mostrar =
                campo.type === 'password';

            campo.type =
                mostrar ? 'text' : 'password';

            boton.textContent =
                mostrar ? '🙈' : '👁';

            boton.setAttribute(
                'aria-label',
                mostrar
                    ? 'Ocultar contraseña'
                    : 'Mostrar contraseña'
            );

        });
    }

    configurarTogglePassword(
        togglePassword,
        password
    );

    configurarTogglePassword(
        togglePasswordConfirm,
        passwordConfirm
    );


    /* =========================================================
       MOSTRAR ERROR GENERAL
    ========================================================== */

    function mostrarErrorGeneral(mensaje) {

        if (!generalError) {
            return;
        }

        generalError.textContent = mensaje;
        generalError.style.display = 'block';
    }


    /* =========================================================
       OCULTAR ERROR GENERAL
    ========================================================== */

    function ocultarErrorGeneral() {

        if (!generalError) {
            return;
        }

        generalError.textContent = '';
        generalError.style.display = 'none';
    }


    /* =========================================================
       MOSTRAR ERROR DE CAMPO
    ========================================================== */

    function mostrarError(campo, mensaje) {

        limpiarError(campo);

        campo.classList.add('input-error');

        const error =
            document.createElement('small');

        error.className = 'field-error';
        error.textContent = mensaje;

        campo.parentElement.appendChild(error);
    }


    /* =========================================================
       MOSTRAR CAMPO VÁLIDO
    ========================================================== */

    function mostrarValido(campo) {

        limpiarError(campo);

        campo.classList.add('input-valid');
    }


    /* =========================================================
       LIMPIAR ERROR
    ========================================================== */

    function limpiarError(campo) {

        campo.classList.remove(
            'input-error',
            'input-valid'
        );

        const error =
            campo.parentElement.querySelector(
                '.field-error'
            );

        if (error) {
            error.remove();
        }

        const success =
            campo.parentElement.querySelector(
                '.field-success'
            );

        if (success) {
            success.remove();
        }
    }


    /* =========================================================
       VALIDAR NOMBRE
    ========================================================== */

    function validarNombre() {

        const valor = nombre.value.trim();

        limpiarError(nombre);

        if (valor === '') {

            mostrarError(
                nombre,
                'Introduce tu nombre.'
            );

            return false;
        }

        if (valor.length < 2) {

            mostrarError(
                nombre,
                'El nombre debe tener al menos 2 caracteres.'
            );

            return false;
        }

        if (valor.length > 50) {

            mostrarError(
                nombre,
                'El nombre no puede superar los 50 caracteres.'
            );

            return false;
        }

        const regex =
            /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñÀ-ÿ\s'-]+$/;

        if (!regex.test(valor)) {

            mostrarError(
                nombre,
                'El nombre contiene caracteres no válidos.'
            );

            return false;
        }

        mostrarValido(nombre);

        return true;
    }


    /* =========================================================
       VALIDAR APELLIDOS
    ========================================================== */

    function validarApellidos() {

        const valor = apellidos.value.trim();

        limpiarError(apellidos);

        if (valor === '') {

            mostrarError(
                apellidos,
                'Introduce tus apellidos.'
            );

            return false;
        }

        if (valor.length < 2) {

            mostrarError(
                apellidos,
                'Los apellidos deben tener al menos 2 caracteres.'
            );

            return false;
        }

        if (valor.length > 100) {

            mostrarError(
                apellidos,
                'Los apellidos no pueden superar los 100 caracteres.'
            );

            return false;
        }

        const regex =
            /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñÀ-ÿ\s'-]+$/;

        if (!regex.test(valor)) {

            mostrarError(
                apellidos,
                'Los apellidos contienen caracteres no válidos.'
            );

            return false;
        }

        mostrarValido(apellidos);

        return true;
    }


    /* =========================================================
       VALIDAR USUARIO
    ========================================================== */

    function validarUsuario() {

        const valor = usuario.value.trim();

        limpiarError(usuario);

        if (valor === '') {

            mostrarError(
                usuario,
                'Introduce un nombre de usuario.'
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

        if (valor.length > 30) {

            mostrarError(
                usuario,
                'El usuario no puede superar los 30 caracteres.'
            );

            return false;
        }

        const regex =
            /^[a-zA-Z0-9._-]+$/;

        if (!regex.test(valor)) {

            mostrarError(
                usuario,
                'El usuario solo puede contener letras, números, punto, guion y guion bajo.'
            );

            return false;
        }

        mostrarValido(usuario);

        return true;
    }


    /* =========================================================
       VALIDAR EMAIL
    ========================================================== */

    function validarEmail() {

        const valor = email.value.trim();

        limpiarError(email);

        if (valor === '') {

            mostrarError(
                email,
                'Introduce un correo electrónico.'
            );

            return false;
        }

        if (valor.length > 100) {

            mostrarError(
                email,
                'El correo electrónico es demasiado largo.'
            );

            return false;
        }

        const regex =
            /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        if (!regex.test(valor)) {

            mostrarError(
                email,
                'Introduce un correo electrónico válido.'
            );

            return false;
        }

        mostrarValido(email);

        return true;
    }


    
/* =========================================================
   VALIDAR CONTRASEÑA
========================================================= */

function validarPassword() {

    const valor = password.value;

    limpiarError(password);


    /* -----------------------------------------------
       CAMPO VACÍO
    ------------------------------------------------ */

    if (valor === '') {

        mostrarError(
            password,
            'Introduce una contraseña.'
        );

        return false;
    }


    /* -----------------------------------------------
       LONGITUD MÍNIMA
    ------------------------------------------------ */

    if (valor.length < 8) {

        mostrarError(
            password,
            'La contraseña debe tener al menos 8 caracteres.'
        );

        return false;
    }


    /* -----------------------------------------------
       LONGITUD MÁXIMA
    ------------------------------------------------ */

    if (valor.length > 128) {

        mostrarError(
            password,
            'La contraseña no puede superar los 128 caracteres.'
        );

        return false;
    }


    /* -----------------------------------------------
       DEBE CONTENER UNA LETRA
    ------------------------------------------------ */

    if (!/[A-Za-zÁÉÍÓÚáéíóúÜüÑñ]/.test(valor)) {

        mostrarError(
            password,
            'La contraseña debe incluir al menos una letra.'
        );

        return false;
    }


    /* -----------------------------------------------
       DEBE CONTENER UN NÚMERO
    ------------------------------------------------ */

    if (!/[0-9]/.test(valor)) {

        mostrarError(
            password,
            'La contraseña debe incluir al menos un número.'
        );

        return false;
    }


    /* -----------------------------------------------
       DEBE CONTENER UN CARÁCTER ESPECIAL
    ------------------------------------------------ */

    if (!/[!@#$%^&*(),.?":{}|<>_\-+=\\[\]\/;'`~]/.test(valor)) {

        mostrarError(
            password,
            'La contraseña debe incluir al menos un carácter especial (por ejemplo: !, @, #, $, %).'
        );

        return false;
    }


    /* -----------------------------------------------
       TODO CORRECTO
    ------------------------------------------------ */

    mostrarValido(password);

    return true;
}


/* =========================================================
   VALIDAR CONFIRMACIÓN DE CONTRASEÑA
========================================================= */

function validarPasswordConfirm() {

    const valor = passwordConfirm.value;

    limpiarError(passwordConfirm);


    /* -----------------------------------------------
       CAMPO VACÍO
    ------------------------------------------------ */

    if (valor === '') {

        mostrarError(
            passwordConfirm,
            'Repite la contraseña.'
        );

        return false;
    }


    /* -----------------------------------------------
       COMPROBAR COINCIDENCIA
    ------------------------------------------------ */

    if (valor !== password.value) {

        mostrarError(
            passwordConfirm,
            'Las contraseñas no coinciden.'
        );

        return false;
    }


    /* -----------------------------------------------
       TODO CORRECTO
    ------------------------------------------------ */

    mostrarValido(passwordConfirm);

    return true;
}




    /* =========================================================
       VALIDAR TÉRMINOS
    ========================================================== */

    function validarTerminos() {

        const grupo =
            terminos.closest('.terms');

        if (!terminos.checked) {

            if (grupo) {
                grupo.style.color = '#b42331';
            }

            return false;
        }

        if (grupo) {
            grupo.style.color = '#526078';
        }

        return true;
    }


    /* =========================================================
       VALIDACIÓN COMPLETA
    ========================================================== */

    function validarFormulario() {

        ocultarErrorGeneral();

        const nombreValido =
            validarNombre();

        const apellidosValidos =
            validarApellidos();

        const usuarioValido =
            validarUsuario();

        const emailValido =
            validarEmail();

        const passwordValida =
            validarPassword();

        const confirmacionValida =
            validarPasswordConfirm();

        const terminosValidos =
            validarTerminos();


        return (
            nombreValido &&
            apellidosValidos &&
            usuarioValido &&
            emailValido &&
            passwordValida &&
            confirmacionValida &&
            terminosValidos
        );
    }


    /* =========================================================
       ENVÍO DEL FORMULARIO
    ========================================================== */

    form.addEventListener('submit', (event) => {

        event.preventDefault();

        ocultarErrorGeneral();


        const formularioValido =
            validarFormulario();


        /* -----------------------------------------------
           FORMULARIO INCORRECTO
        ------------------------------------------------ */

        if (!formularioValido) {

            mostrarErrorGeneral(
                'Revisa los datos introducidos antes de continuar.'
            );

            const primerError =
                form.querySelector('.input-error');

            if (primerError) {
                primerError.focus();
            }

            return;
        }


        /* -----------------------------------------------
           FORMULARIO CORRECTO
        ------------------------------------------------ */

        console.log(
            'Formulario válido. Enviando registro...'
        );


        /*
         * Permitimos el envío normal del formulario.
         *
         * action="signin.php"
         * method="POST"
         */

        HTMLFormElement.prototype.submit.call(form);

    });


    /* =========================================================
       VALIDACIÓN AL SALIR DE LOS CAMPOS
    ========================================================== */

    nombre.addEventListener(
        'blur',
        validarNombre
    );

    apellidos.addEventListener(
        'blur',
        validarApellidos
    );

    usuario.addEventListener(
        'blur',
        validarUsuario
    );

    email.addEventListener(
        'blur',
        validarEmail
    );

    password.addEventListener(
        'blur',
        validarPassword
    );

    passwordConfirm.addEventListener(
        'blur',
        validarPasswordConfirm
    );


    /* =========================================================
       VALIDACIÓN MIENTRAS SE ESCRIBE
    ========================================================== */

    nombre.addEventListener('input', () => {

        if (nombre.classList.contains('input-error')) {
            validarNombre();
        }

    });

    apellidos.addEventListener('input', () => {

        if (apellidos.classList.contains('input-error')) {
            validarApellidos();
        }

    });

    usuario.addEventListener('input', () => {

        if (usuario.classList.contains('input-error')) {
            validarUsuario();
        }

    });

    email.addEventListener('input', () => {

        if (email.classList.contains('input-error')) {
            validarEmail();
        }

    });

    password.addEventListener('input', () => {

        if (password.classList.contains('input-error')) {
            validarPassword();
        }

        if (passwordConfirm.value !== '') {
            validarPasswordConfirm();
        }

    });

    passwordConfirm.addEventListener('input', () => {

        if (
            passwordConfirm.classList.contains(
                'input-error'
            )
        ) {
            validarPasswordConfirm();
        }

    });


    /* =========================================================
       TÉRMINOS
    ========================================================== */

    terminos.addEventListener(
        'change',
        validarTerminos
    );

});


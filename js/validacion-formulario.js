/* =========================================================
   VALIDACIÓN DE FORMULARIOS (COMÚN)
   =========================================================
   Mismo comportamiento que clientes.js / usuarios.js /
   empresas.js / comercializadoras.js, para los formularios
   que no tienen validación propia (planificador,
   configuración):

   - Al salir de un campo (blur) se valida y, si falla, se
     marca en rojo (.input-error) con su mensaje en el
     span#error-<name>.
   - Mientras se corrige (input/change), el error se quita en
     cuanto el valor es válido.
   - Al enviar con errores: no se envía, se marcan todos los
     campos que fallan y se muestra el aviso general en el
     .form-error-general del formulario.

   Uso:
     inicializarValidacionFormulario(formulario, {
         nombre_campo: valor => 'mensaje' | null,
         ...
     });
========================================================= */

const MENSAJE_FORMULARIO_CON_ERRORES = 'El formulario contiene errores. Revísalos antes de enviarlo.';

function inicializarValidacionFormulario(formulario, validadores) {

    if (!formulario) {
        return;
    }

    const mensajeGeneral = formulario.querySelector('.form-error-general');

    const campo = nombre => formulario.elements[nombre] || null;

    function validarCampo(nombre) {

        const input = campo(nombre);

        if (!input) {
            return true;
        }

        const error = validadores[nombre](input.value.trim(), input);
        const contenedorError = formulario.querySelector('#error-' + nombre);

        input.classList.toggle('input-error', Boolean(error));
        input.setAttribute('aria-invalid', error ? 'true' : 'false');

        if (contenedorError) {
            contenedorError.textContent = error || '';
        }

        return !error;

    }

    function mostrarMensajeGeneral() {

        if (!mensajeGeneral) {
            return;
        }

        mensajeGeneral.textContent = MENSAJE_FORMULARIO_CON_ERRORES;
        mensajeGeneral.style.display = 'block';

    }

    function ocultarMensajeGeneral() {

        if (!mensajeGeneral) {
            return;
        }

        mensajeGeneral.textContent = '';
        mensajeGeneral.style.display = 'none';

    }

    function quedanErrores() {

        return Object.keys(validadores).some(nombre => {

            const input = campo(nombre);

            return input && validadores[nombre](input.value.trim(), input) !== null;

        });

    }

    Object.keys(validadores).forEach(nombre => {

        const input = campo(nombre);

        if (!input) {
            return;
        }

        input.addEventListener('blur', () => validarCampo(nombre));

        // Solo se QUITA el error mientras se escribe (no se
        // marca uno nuevo hasta salir del campo).
        const alCambiar = () => {

            if (input.classList.contains('input-error') && validadores[nombre](input.value.trim(), input) === null) {
                validarCampo(nombre);
            }

            if (!quedanErrores()) {
                ocultarMensajeGeneral();
            }

        };

        input.addEventListener('input', alCambiar);
        input.addEventListener('change', alCambiar);

    });

    formulario.addEventListener('submit', event => {

        // Se validan TODOS (sin cortar en el primero) para
        // marcarlos todos a la vez.
        const resultados = Object.keys(validadores).map(validarCampo);

        if (resultados.every(Boolean)) {

            ocultarMensajeGeneral();

            // Evita el doble envío.
            const boton = formulario.querySelector('button[type="submit"]');

            if (boton) {
                boton.disabled = true;
            }

            return;

        }

        event.preventDefault();

        mostrarMensajeGeneral();

        const primerError = formulario.querySelector('.input-error');

        if (mensajeGeneral) {
            mensajeGeneral.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (primerError) {
            primerError.focus();
        }

    });

}

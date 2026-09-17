(function () {

    /* =========================================================
       BLOQUEO AUTOMÁTICO POR INACTIVIDAD
       El tiempo (en minutos) viene fijado en window.BLOQUEO_
       AUTOMATICO_MINUTOS, escrito por templates/header.php
       según lo configurado en Configuración. 0 = "Nunca" (desactivado).
    ========================================================= */

    var minutos = Number(window.BLOQUEO_AUTOMATICO_MINUTOS);

    if (!minutos || minutos <= 0) {
        return;
    }

    var TIEMPO_INACTIVIDAD = minutos * 60 * 1000;
    var temporizador = null;

    function bloquearPorInactividad() {
        window.location.href = '/comercializadora/views/login/bloquear.php';
    }

    function reiniciarTemporizador() {

        if (temporizador) {
            clearTimeout(temporizador);
        }

        temporizador = setTimeout(bloquearPorInactividad, TIEMPO_INACTIVIDAD);

    }

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click']
        .forEach(function (evento) {
            document.addEventListener(evento, reiniciarTemporizador, { passive: true });
        });

    reiniciarTemporizador();

})();

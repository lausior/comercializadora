/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 07_intentos_login.sql
-- -- DESCRIPCIÓN: Ajustes de bloqueo por intentos de login
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- VALORES INICIALES: INTENTOS Y BLOQUEO
-- =====================================================
--
-- Reutiliza la tabla `configuracion` (clave/valor) ya
-- existente para el bloqueo automático por inactividad.
--
-- intentos_login_max        -> nº de intentos fallidos
--                               permitidos antes de bloquear
--                               el acceso (por defecto 5).
-- minutos_bloqueo_intentos  -> minutos que permanece
--                               bloqueado el acceso tras
--                               agotar los intentos (por
--                               defecto 15).

INSERT INTO configuracion (clave, valor) VALUES
('intentos_login_max', '5'),
('minutos_bloqueo_intentos', '15'); */

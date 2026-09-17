/* -- -- =====================================================
-- -- PROYECTO: COMPARADORA
-- -- ARCHIVO: 09_retencion_logs.sql
-- -- DESCRIPCIÓN: Ajustes de retención/borrado de logs
-- -- =====================================================

-- USE comparadora;


-- =====================================================
-- VALORES INICIALES: RETENCIÓN DE LOGS
-- =====================================================
--
-- Reutiliza la tabla `configuracion` (clave/valor) ya
-- existente.
--
-- logs_retencion_dias  -> días que se conservan los logs
--                          antes de borrarse. 0 = "Nunca"
--                          (no se borran automáticamente).
-- logs_ultimo_borrado  -> fecha y hora (DATETIME) del último
--                          borrado automático realizado. Se
--                          consulta al iniciar sesión para no
--                          tener que ejecutar el borrado en
--                          cada login, solo cuando ya ha
--                          pasado suficiente tiempo desde el
--                          último.

INSERT INTO configuracion (clave, valor) VALUES
('logs_retencion_dias', '0'); */

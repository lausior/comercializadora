<?php

// =====================================================
// "SEGURIDAD" YA NO ES UNA PÁGINA PROPIA
// =====================================================
//
// El "Cambiar contraseña" que vivía aquí ahora es un modal
// disponible desde cualquier pantalla (ver
// templates/sidebar.php), abierto desde el botón "Seguridad"
// del menú. Este archivo se deja como redirección, por si
// alguien tiene la URL antigua guardada: manda al panel y,
// con "?abrir=seguridad", el propio sidebar abre el modal
// solo al cargar.
//
// =====================================================

header('Location: /comercializadora/index.php?abrir=seguridad');
exit;

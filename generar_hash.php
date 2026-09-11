<?php

// =====================================================
// SCRIPT TEMPORAL — BORRAR DESPUÉS DE USARLO
// =====================================================
//
// Sirve solo para generar el hash de una contraseña y
// poder pegarlo a mano en un INSERT de la tabla usuarios.
// No debe quedarse en el servidor una vez usado.
//
// Uso: generar_hash.php?password=loquesea
//
// =====================================================

if (!isset($_GET['password']) || $_GET['password'] === '') {

    echo 'Añade el parámetro en la URL, por ejemplo:<br>';
    echo 'generar_hash.php?password=123456';
    exit;

}

$hash = password_hash($_GET['password'], PASSWORD_DEFAULT);

echo '<p>Contraseña: <strong>' . htmlspecialchars($_GET['password']) . '</strong></p>';
echo '<p>Hash generado:</p>';
echo '<textarea style="width:100%;height:60px;">' . htmlspecialchars($hash) . '</textarea>';
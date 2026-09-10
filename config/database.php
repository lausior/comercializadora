<?php

// ============================================
// CONEXIÓN CON LA BASE DE DATOS
// ============================================

// Datos de conexión
$host = 'localhost';
$dbname = 'comparadora';
$username = 'root';
$password = '';

try {

    // Crear conexión PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",$username,$password
    );

    // Configurar PDO para que muestre errores mediante excepciones
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Devolver los resultados como arrays asociativos
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    // Mostrar mensaje si la conexión falla
    die("Error de conexión con la base de datos: " . $e->getMessage());
}




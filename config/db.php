<?php
// Conexión a la base de datos
global $conn;
$conn = new mysqli(
    "localhost", // ip o nombre del servidor
    "root", // usuario
    "", // contraseña
    "finanzas_app" // nombre de la base de datos
);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer codificación UTF-8
$conn->set_charset("utf8");
?>

<?php
// Conexion a la base de datos
// Cambia estos datos si tu configuracion es diferente

$host     = "localhost";
$bbdd     = "animalia";
$usuario  = "root";
$password = "";

// Crear la conexion con mysqli
$conexion = new mysqli($host, $usuario, $password, $bbdd);

// Comprobar si hay error de conexion
if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}

// Para que funcione bien con caracteres especiales (tildes, ñ...)
$conexion->set_charset("utf8mb4");


// Definir una constante con la URL de la aplicacion.

define('conexion', 'http://localhost/Animalia/');
?>

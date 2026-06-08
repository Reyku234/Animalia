<?php
session_start();

// Destruir todos los datos de la sesion
session_unset();
session_destroy();

// Redirigir al inicio
header("Location: ../index.php");
exit;
?>

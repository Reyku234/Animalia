<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Solo se puede acceder por POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirigir('../index.php');
}

// Tiene que estar logueado
if (!logeado()) {
    redirigir('../paginas/login.php');
}

$id_animal  = (int)$_POST['id_animal'];
$id_usuario = (int)$_SESSION['id_usuario'];
$comentarios = isset($_POST['comentarios']) ? limpiar($_POST['comentarios']) : null;

// Comprobar que el animal existe y esta disponible
$stmt = $conexion->prepare("SELECT id_animal, estado_adopcion FROM animales WHERE id_animal = ?");
$stmt->bind_param("i", $id_animal);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();

if (!$animal) {
    redirigir('../index.php');
}

if ($animal['estado_adopcion'] != 'disponible') {
    redirigir('../paginas/detalle_animal.php?id=' . $id_animal . '&error=' . urlencode('Este animal ya no está disponible.'));
}

// Comprobar que no tenga ya una solicitud activa para este animal
$stmtDup = $conexion->prepare(
    "SELECT id_solicitud FROM solicitudes_adopcion
     WHERE id_usuario = ? AND id_animal = ?
     AND estado NOT IN ('rechazada', 'finalizada')"
);
$stmtDup->bind_param("ii", $id_usuario, $id_animal);
$stmtDup->execute();

if ($stmtDup->get_result()->num_rows > 0) {
    redirigir('../paginas/detalle_animal.php?id=' . $id_animal . '&error=' . urlencode('Ya tienes una solicitud activa para este animal.'));
}

// Insertar la solicitud
$stmtInsert = $conexion->prepare(
    "INSERT INTO solicitudes_adopcion (id_usuario, id_animal, estado, comentarios)
     VALUES (?, ?, 'pendiente', ?)"
);
$stmtInsert->bind_param("iis", $id_usuario, $id_animal, $comentarios);

if ($stmtInsert->execute()) {
    // Cambiar el estado del animal a reservado
    $stmtUpdate = $conexion->prepare("UPDATE animales SET estado_adopcion = 'reservado' WHERE id_animal = ?");
    $stmtUpdate->bind_param("i", $id_animal);
    $stmtUpdate->execute();

    redirigir('../paginas/detalle_animal.php?id=' . $id_animal . '&exito=1');
} else {
    redirigir('../paginas/detalle_animal.php?id=' . $id_animal . '&error=' . urlencode('Error al enviar la solicitud. Inténtalo de nuevo.'));
}
?>
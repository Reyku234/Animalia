<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Solo pueden entrar usuarios logueados
if (!logeado()) {
    redirigir('../paginas/login.php');
}

$id_usuario = (int)$_SESSION['id_usuario'];

// Obtener las solicitudes del usuario con los datos del animal
$stmt = $conexion->prepare(
    "SELECT sa.*,
            a.nombre AS animal_nombre,
            a.especie AS animal_especie,
            u2.nombre AS responsable_nombre,
            u2.apellidos AS responsable_apellidos
     FROM solicitudes_adopcion sa
     JOIN animales a ON a.id_animal = sa.id_animal
     LEFT JOIN usuarios u2 ON u2.id_usuario = sa.id_responsable
     WHERE sa.id_usuario = ?
     ORDER BY sa.fecha_solicitud DESC"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$total = 0;
$activas = 0;
$aprobadas = 0;
$rechazadas = 0;
$solicitudes = array();

while ($fila = $resultado->fetch_assoc()) {
    $solicitudes[] = $fila;
    $total++;
    if ($fila['estado'] == 'pendiente' || $fila['estado'] == 'en_revision') $activas++;
    if ($fila['estado'] == 'aprobada')  $aprobadas++;
    if ($fila['estado'] == 'rechazada') $rechazadas++;
}

// Cancelar una solicitud
$mensajeCancelar = "";
if (isset($_GET['cancelar']) && is_numeric($_GET['cancelar'])) {
    $id_solicitud = (int)$_GET['cancelar'];

    $stmtCheck = $conexion->prepare(
        "SELECT sa.id_solicitud, sa.id_animal FROM solicitudes_adopcion sa
         WHERE sa.id_solicitud = ? AND sa.id_usuario = ? AND sa.estado = 'pendiente'"
    );
    $stmtCheck->bind_param("ii", $id_solicitud, $id_usuario);
    $stmtCheck->execute();
    $solCheck = $stmtCheck->get_result()->fetch_assoc();

    if ($solCheck) {
        // Borrar la solicitud
        $stmtDel = $conexion->prepare("DELETE FROM solicitudes_adopcion WHERE id_solicitud = ?");
        $stmtDel->bind_param("i", $id_solicitud);
        $stmtDel->execute();

        $stmtOtras = $conexion->prepare(
            "SELECT COUNT(*) AS total FROM solicitudes_adopcion
             WHERE id_animal = ? AND estado NOT IN ('rechazada', 'finalizada')"
        );
        $stmtOtras->bind_param("i", $solCheck['id_animal']);
        $stmtOtras->execute();
        $otrasResult = $stmtOtras->get_result()->fetch_assoc();

        if ($otrasResult['total'] == 0) {
            $stmtAnimal = $conexion->prepare("UPDATE animales SET estado_adopcion = 'disponible' WHERE id_animal = ?");
            $stmtAnimal->bind_param("i", $solCheck['id_animal']);
            $stmtAnimal->execute();
        }

        redirigir('../paginas/mis_solicitudes.php?cancelado=1');
    }
}

$cancelado = isset($_GET['cancelado']) ? true : false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis solicitudes - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="cabecera-pagina">
    <div class="contenedor">
        <h1>Mis solicitudes</h1>
    </div>
</div>

<div class="contenedor">

    <?php if ($cancelado): ?>
        <div class="alerta alerta-exito">Solicitud cancelada correctamente.</div>
    <?php endif; ?>

  
    <div class="estadisticas">
        <div class="stat">
            <div class="numero"><?= $total ?></div>
            <div class="etiqueta">Total</div>
        </div>
        <div class="stat">
            <div class="numero"><?= $activas ?></div>
            <div class="etiqueta">Activas</div>
        </div>
        <div class="stat">
            <div class="numero"><?= $aprobadas ?></div>
            <div class="etiqueta">Aprobadas</div>
        </div>
        <div class="stat">
            <div class="numero"><?= $rechazadas ?></div>
            <div class="etiqueta">Rechazadas</div>
        </div>
    </div>

    <?php if ($total == 0): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--texto-gris);">
            <div style="font-size: 3.5rem; margin-bottom: 15px;">🐾</div>
            <h3>Aún no has hecho ninguna solicitud</h3>
            <p style="margin: 10px 0 20px;">Explora los animales disponibles y encuentra tu compañero ideal.</p>
            <a href="../index.php" class="btn btn-verde">Ver animales</a>
        </div>
    <?php else: ?>
        <?php foreach ($solicitudes as $sol): ?>
            <div class="tarjeta-solicitud">

                <div class="sol-emoji">
                    <?= icono_especie($sol['animal_especie']) ?>
                </div>

                <div class="sol-info">
                    <h3>
                        <?= limpiar($sol['animal_nombre']) ?>
                        <small style="font-size: 0.75rem; color: var(--texto-gris); font-weight: normal;">
                            (<?= limpiar($sol['animal_especie']) ?>)
                        </small>
                    </h3>
                    <div class="sol-meta">
                        📅 Solicitado el <?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?>
                        <?php if ($sol['responsable_nombre']): ?>
                            &nbsp;·&nbsp; 👤 Responsable: <?= limpiar($sol['responsable_nombre']) ?> <?= limpiar($sol['responsable_apellidos']) ?>
                        <?php endif; ?>
                    </div>
                    <?= badge_estado($sol['estado']) ?>
                    <?php if ($sol['comentarios']): ?>
                        <div class="sol-comentario">💬 <?= limpiar($sol['comentarios']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="sol-acciones">
                    <?php if ($sol['estado'] == 'pendiente'): ?>
                        <a href="mis_solicitudes.php?cancelar=<?= $sol['id_solicitud'] ?>"
                           class="btn btn-pequeño btn-rojo"
                           onclick="return confirm('¿Seguro que quieres cancelar esta solicitud?')">
                            Cancelar
                        </a>
                    <?php endif; ?>
 
                    <span style="font-size: 0.75rem; color: var(--texto-gris);">
                        <?php
                        if ($sol['estado'] == 'pendiente')    echo "⏳ En espera";
                        if ($sol['estado'] == 'en_revision')  echo "🔍 Revisando";
                        if ($sol['estado'] == 'aprobada')     echo "✅ ¡Aprobada!";
                        if ($sol['estado'] == 'rechazada')    echo "❌ Rechazada";
                        if ($sol['estado'] == 'finalizada')   echo "🏡 Completada";
                        ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<footer>
   
    <p>
         2025 Animalia. Este sitio está distribuido bajo licencia
        
             <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">
            Creative Commons BY-SA 4.0
        </a>
        <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">
        
    </a>
    </p>
    <img src="https://licensebuttons.net/l/by-sa/4.0/88x31.png"
             alt="Licencia CC BY-SA">
    <p>

    </p>

    
</footer>

</body>
</html>
<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

if (!logeado() || (!rol_usuario('administrador') && !rol_usuario('trabajador'))) {
    redirigir('../paginas/login.php');
}


if (isset($_GET['eliminar']) && rol_usuario('administrador')) {
    $id = (int)$_GET['eliminar'];

    $stmt = $conexion->prepare("SELECT id_animal FROM solicitudes_adopcion WHERE id_solicitud = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sol = $stmt->get_result()->fetch_assoc();

    if ($sol) {
        $conexion->prepare("DELETE FROM solicitudes_adopcion WHERE id_solicitud = ?")->bind_param("i", $id);
        $conexion->prepare("DELETE FROM solicitudes_adopcion WHERE id_solicitud = ?")->execute();

  
        $stmtDel = $conexion->prepare("DELETE FROM solicitudes_adopcion WHERE id_solicitud = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();

        $stmtContar = $conexion->prepare("SELECT COUNT(*) AS total FROM solicitudes_adopcion WHERE id_animal = ? AND estado NOT IN ('rechazada', 'finalizada')");
        $stmtContar->bind_param("i", $sol['id_animal']);
        $stmtContar->execute();
        $contar = $stmtContar->get_result()->fetch_assoc();

        if ($contar['total'] == 0) {
            $stmtAnimal = $conexion->prepare("UPDATE animales SET estado_adopcion = 'disponible' WHERE id_animal = ?");
            $stmtAnimal->bind_param("i", $sol['id_animal']);
            $stmtAnimal->execute();
        }

    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {

    $id_solicitud   = (int)$_POST['id_solicitud'];
    $nuevo_estado   = $_POST['nuevo_estado'];
    $comentarios    = limpiar($_POST['comentarios']);
    $id_responsable = (int)$_SESSION['id_usuario'];

    $estadosValidos = array('pendiente', 'en_revision', 'aprobada', 'rechazada', 'finalizada');

    if (!in_array($nuevo_estado, $estadosValidos)) {
    } else {
        $stmtSol = $conexion->prepare("SELECT * FROM solicitudes_adopcion WHERE id_solicitud = ?");
        $stmtSol->bind_param("i", $id_solicitud);
        $stmtSol->execute();
        $solicitud = $stmtSol->get_result()->fetch_assoc();

        if ($solicitud) {
            $stmtUpdate = $conexion->prepare("UPDATE solicitudes_adopcion SET estado = ?, comentarios = ?, id_responsable = ? WHERE id_solicitud = ?");
            $stmtUpdate->bind_param("ssii", $nuevo_estado, $comentarios, $id_responsable, $id_solicitud);
            $stmtUpdate->execute();

            $id_animal = $solicitud['id_animal'];

            if ($nuevo_estado == 'aprobada' || $nuevo_estado == 'finalizada') {
                $conexion->prepare("UPDATE animales SET estado_adopcion = 'adoptado' WHERE id_animal = ?")->bind_param("i", $id_animal);
                $stmtA = $conexion->prepare("UPDATE animales SET estado_adopcion = 'adoptado' WHERE id_animal = ?");
                $stmtA->bind_param("i", $id_animal);
                $stmtA->execute();

                if ($nuevo_estado == 'aprobada') {
                    $stmtOtras = $conexion->prepare("UPDATE solicitudes_adopcion SET estado = 'rechazada', comentarios = CONCAT(IFNULL(comentarios,''), ' [Rechazada: otro adoptante fue aprobado.]') WHERE id_animal = ? AND id_solicitud != ? AND estado NOT IN ('rechazada', 'finalizada')");
                    $stmtOtras->bind_param("ii", $id_animal, $id_solicitud);
                    $stmtOtras->execute();
                }

            } elseif ($nuevo_estado == 'rechazada') {
                $stmtContar = $conexion->prepare("SELECT COUNT(*) AS total FROM solicitudes_adopcion WHERE id_animal = ? AND id_solicitud != ? AND estado NOT IN ('rechazada', 'finalizada')");
                $stmtContar->bind_param("ii", $id_animal, $id_solicitud);
                $stmtContar->execute();
                $contar = $stmtContar->get_result()->fetch_assoc();

                $nuevoEstadoAnimal = ($contar['total'] > 0) ? 'reservado' : 'disponible';
                $stmtA = $conexion->prepare("UPDATE animales SET estado_adopcion = ? WHERE id_animal = ?");
                $stmtA->bind_param("si", $nuevoEstadoAnimal, $id_animal);
                $stmtA->execute();

            } elseif ($nuevo_estado == 'en_revision') {
                $stmtA = $conexion->prepare("UPDATE animales SET estado_adopcion = 'reservado' WHERE id_animal = ?");
                $stmtA->bind_param("i", $id_animal);
                $stmtA->execute();
            }
      
        }
    }
}


$filtroEstado  = isset($_GET['estado'])  ? $_GET['estado']  : '';
$filtroUsuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';

$condiciones = array("1=1");
$params      = array();
$tipos       = "";

if ($filtroEstado != '') {
    $condiciones[] = "sa.estado = ?";
    $tipos        .= "s";
    $params[]      = $filtroEstado;
}

if ($filtroUsuario != '') {
    $condiciones[] = "(u.nombre LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)";
    $tipos        .= "sss";
    $buscar        = "%" . $filtroUsuario . "%";
    $params[]      = $buscar;
    $params[]      = $buscar;
    $params[]      = $buscar;
}

$sql = "SELECT sa.*, a.nombre AS animal_nombre, a.especie AS animal_especie,
               u.nombre AS adoptante_nombre, u.apellidos AS adoptante_apellidos, u.email AS adoptante_email,
               r.nombre AS resp_nombre, r.apellidos AS resp_apellidos
        FROM solicitudes_adopcion sa
        JOIN animales a ON a.id_animal = sa.id_animal
        JOIN usuarios u ON u.id_usuario = sa.id_usuario
        LEFT JOIN usuarios r ON r.id_usuario = sa.id_responsable
        WHERE " . implode(" AND ", $condiciones) . "
        ORDER BY sa.fecha_solicitud DESC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) $stmt->bind_param($tipos, ...$params);
$stmt->execute();
$resultado   = $stmt->get_result();
$solicitudes = array();
while ($fila = $resultado->fetch_assoc()) $solicitudes[] = $fila;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="cabecera_pagina">
    <div class="contenedor">
        <h1>Panel de administración</h1><br>
    </div>
</div>

<div class="contenedor">



    <form method="GET" action="admin_solicitudes.php" style="margin-bottom: 20px;">
        <div class="filtros">
            <div class="form-grupo">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todas</option>
                    <option value="pendiente"   <?= $filtroEstado == 'pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                    <option value="en_revision" <?= $filtroEstado == 'en_revision' ? 'selected' : '' ?>>En revisión</option>
                    <option value="aprobada"    <?= $filtroEstado == 'aprobada'    ? 'selected' : '' ?>>Aprobada</option>
                    <option value="rechazada"   <?= $filtroEstado == 'rechazada'   ? 'selected' : '' ?>>Rechazada</option>
                    <option value="finalizada"  <?= $filtroEstado == 'finalizada'  ? 'selected' : '' ?>>Finalizada</option>
                </select>
            </div>
            <div class="form-grupo">
                <label>Usuario</label>
                <input type="text" name="usuario" placeholder="Nombre, apellidos o email..." value="<?= limpiar($filtroUsuario) ?>">
            </div>
            <button type="submit" class="btn btn-verde">Filtrar</button>
            <a href="admin_solicitudes.php" class="btn btn-outline">Ver todas</a>
        </div>
    </form>

    <?php if (count($solicitudes) == 0): ?>
        <p style="text-align: center; padding: 50px; color: #5A7068;">No hay solicitudes con este filtro.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Animal</th>
                        <th>Adoptante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <tr>
                            <td><?= $sol['id_solicitud'] ?></td>
                            <td>
                                <strong><?= limpiar($sol['animal_nombre']) ?></strong><br>
                                <small style="color: #5A7068;"><?= limpiar($sol['animal_especie']) ?></small>
                            </td>
                            <td>
                                <?= limpiar($sol['adoptante_nombre']) ?> <?= limpiar($sol['adoptante_apellidos']) ?><br>
                                <small style="color: #5A7068;"><?= limpiar($sol['adoptante_email']) ?></small>
                            </td>
                            <td><?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></td>
                            <td><?= badge_estado($sol['estado']) ?></td>
                            <td><?= $sol['resp_nombre'] ? limpiar($sol['resp_nombre']) . ' ' . limpiar($sol['resp_apellidos']) : '—' ?></td>
                            <td style="white-space: nowrap;">
                                <button class="btn btn-pequeño btn-verde" 
                                        onclick="abrirModal(
                                            <?= $sol['id_solicitud'] ?>,
                                            '<?= $sol['estado'] ?>',
                                            '<?= limpiar($sol['animal_nombre']) ?>',
                                            '<?= limpiar($sol['adoptante_nombre']) ?>',
                                            '<?= addslashes($sol['comentarios']) ?>'
                                        )"> Actualizar</button>
                                <?php if (rol_usuario('administrador')): ?>
                                    <a href="admin_solicitudes.php?eliminar=<?= $sol['id_solicitud'] ?><?= $filtroEstado ? '&estado=' . $filtroEstado : '' ?>"
                                       class="btn btn-pequeño btn-rojo"
                                       onclick="return confirm('¿Eliminar esta solicitud?')">
                                        Eliminar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>


<div class="modal-fondo" id="modal_fondo">
    <div class="modal">
        <h3>Actualizar solicitud</h3>
        <div id="info-modal" style="background: #F5F0E8; border-radius: 6px; padding: 10px 14px; margin-bottom: 15px; font-size: 0.88rem; border-left: 3px solid #2C4A3E;"></div>
        <form method="POST" action="admin_solicitudes.php<?= $filtroEstado ? '?estado=' . $filtroEstado : '' ?>">
            <input type="hidden" name="actualizar" value="1">
            <input type="hidden" name="id_solicitud" id="id_solicitud_input">
            <div class="form-grupo">
                <label>Nuevo estado</label>
                <select name="nuevo_estado" id="nuevo_estado_select">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En revisión</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                    <option value="finalizada">Finalizada</option>
                </select>
            </div>
            <div class="form-grupo">
                <label>Comentario</label>
                <textarea name="comentarios" id="comentarios_input" placeholder="Añade una observación..."></textarea>
            </div>
            <div class="modal-botones">
                <button type="button" class="btn btn-outline" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-verde">Guardar</button>
            </div>
        </form>
    </div>
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

<script src="../js/animalia.js"></script>
</body>
</html>
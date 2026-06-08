<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Comprobar que viene un ID por la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirigir('../index.php');
}

$id = (int)$_GET['id'];

// Buscar el animal en la base de datos
$stmt = $conexion->prepare("SELECT * FROM animales WHERE id_animal = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    redirigir('../index.php');
}

$animal = $resultado->fetch_assoc();

// Buscar la entrada del animal
$stmtEntrada = $conexion->prepare("SELECT * FROM entradas_animales WHERE id_animal = ?");
$stmtEntrada->bind_param("i", $id);
$stmtEntrada->execute();
$entrada = $stmtEntrada->get_result()->fetch_assoc();

// Mensaje despues de solicitud
$mensaje = "";
$tipoMensaje = "";

if (isset($_GET['exito'])) {
    $mensaje = "✅ Solicitud de adopción enviada correctamente. Puedes ver su estado en 'Mis solicitudes'.";
    $tipoMensaje = "exito";
}
if (isset($_GET['error'])) {
    $mensaje = "❌ " . limpiar($_GET['error']);
    $tipoMensaje = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= limpiar($animal['nombre']) ?> - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="contenedor">

    <!-- Enlace para volver -->
    <p style="margin: 20px 0;">
        <a href="../index.php" style="color: var(--verde-medio);">← Volver al listado</a>
    </p>

    <?php if ($mensaje): ?>
        <div class="alerta alerta-<?= $tipoMensaje ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Detalle del animal -->
    <div class="detalle_animal">
        <div class="detalle-foto">
            <?= icono_especie($animal['especie']) ?>
        </div>
        <div class="detalle-cuerpo">

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <h1 style="font-size: 2.2rem;"><?= limpiar($animal['nombre']) ?></h1>
                <?= badge_estado($animal['estado_adopcion']) ?>
            </div>

            <p style="color: var(--texto-gris); line-height: 1.7; margin-bottom: 15px;">
                <?= $animal['descripcion'] ? limpiar($animal['descripcion']) : 'Sin descripción disponible.' ?>
            </p>

            <!-- Datos del animal en cuadricula -->
            <div class="detalle-grid">
                <div><span>Especie:</span> <?= limpiar($animal['especie']) ?></div>
                <div><span>Raza:</span> <?= $animal['raza'] ? limpiar($animal['raza']) : '—' ?></div>
                <div><span>Edad:</span> <?= edad($animal['edad']) ?></div>
                <div><span>Sexo:</span> <?= ucfirst($animal['sexo']) ?></div>
                <div><span>Fecha de entrada:</span> <?= $animal['fecha_entrada'] ?></div>
                <?php if ($entrada): ?>
                    <div><span>Procedencia:</span> <?= limpiar($entrada['procedencia']) ?></div>
                <?php endif; ?>
            </div>

            <?php if ($animal['estado_salud']): ?>
                <div class="caja-salud">
                    🏥 <strong>Estado de salud:</strong> <?= limpiar($animal['estado_salud']) ?>
                </div>
            <?php endif; ?>

            <?php if ($entrada && $entrada['observaciones']): ?>
                <div class="caja-salud" style="margin-top: 10px;">
                    📋 <strong>Observaciones de entrada:</strong> <?= limpiar($entrada['observaciones']) ?>
                </div>
            <?php endif; ?>

            <hr class="separador">

            <!-- Boton de adopcion -->
            <?php if ($animal['estado_adopcion'] == 'disponible'): ?>
                <?php if (logeado()): ?>
                    <!-- Formulario para solicitar adopcion -->
                    <form method="POST" action="../paginas/procesar_solicitud.php">
                        <input type="hidden" name="id_animal" value="<?= $animal['id_animal'] ?>">
                        <p style="margin-bottom: 15px; color: var(--texto-gris);">
                            ¿Te gustaría adoptar a <strong><?= limpiar($animal['nombre']) ?></strong>? Puedes añadir un comentario opcional:
                        </p>
                        <div class="form-grupo">
                            <label>Comentario (opcional)</label>
                            <textarea name="comentarios" placeholder="Cuéntanos sobre ti, tu hogar, por qué quieres adoptar..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-verde">🐾 Solicitar adopción</button>
                    </form>
                <?php else: ?>
                    <p style="color: var(--texto-gris); margin-bottom: 15px;">
                        ¿Quieres adoptar a <?= limpiar($animal['nombre']) ?>? Inicia sesión o regístrate primero.
                    </p>
                    <div style="display: flex; gap: 10px;">
                        <a href="../paginas/login.php" class="btn btn-outline">Iniciar sesión</a>
                        <a href="../paginas/registro.php" class="btn btn-verde">Registrarse</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: var(--texto-gris); text-align: center; padding: 15px;">
                    Este animal no está disponible para adopción en este momento.
                </p>
            <?php endif; ?>

        </div>
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

</body>
</html>
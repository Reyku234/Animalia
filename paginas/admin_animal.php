<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Solo admin. 
if (!logeado() || (!rol_usuario('administrador') )) {
    redirigir('../paginas/login.php');
}

$mensaje     = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre        = limpiar($_POST['nombre']);
    $especie       = limpiar($_POST['especie']);
    $raza          = limpiar($_POST['raza']);
    $edad          = (int)$_POST['edad'];
    $sexo          = $_POST['sexo'];
    $estado_salud  = limpiar($_POST['estado_salud']);
    $fecha_entrada = $_POST['fecha_entrada'];
    $descripcion   = limpiar($_POST['descripcion']);

    if (empty($nombre) || empty($especie) || empty($fecha_entrada)) {
        $mensaje     = "El nombre, la especie y la fecha de entrada son obligatorios.";
        $tipoMensaje = "error";
    } else {
        $stmt = $conexion->prepare(
            "INSERT INTO animales (nombre, especie, raza, edad, sexo, estado_salud, fecha_entrada, estado_adopcion, descripcion)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'disponible', ?)"
        );
        $stmt->bind_param("sssissss", $nombre, $especie, $raza, $edad, $sexo, $estado_salud, $fecha_entrada, $descripcion);

        if ($stmt->execute()) {
            $mensaje     = "Animal añadido correctamente.";
            $tipoMensaje = "exito";
        } else {
            $mensaje     = "Error al guardar el animal.";
            $tipoMensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo animal - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="cabecera_pagina">
    <div class="contenedor">
        <h1>Añadir nuevo animal</h1><br>
    </div>
</div>

<div class="contenedor">

    <?php if ($mensaje): ?>
        <div class="alerta alerta_<?= $tipoMensaje ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 12px rgba(44,74,62,0.08); max-width: 650px;">

        <form method="POST" action="admin_animal.php">

            <div class="fila-dos">
                <div class="form-grupo">
                    <label>Nombre *</label>
                    <input type="text" name="nombre"  required
                           value="<?= isset($_POST['nombre']) ? limpiar($_POST['nombre']) : '' ?>">
                </div>
                <div class="form-grupo">
                    <label>Especie *</label>
                    <select name="especie" required>
                        <option value="">Seleccionar</option>
                        <option value="Perro"  <?= (isset($_POST['especie']) && $_POST['especie'] == 'Perro')  ? 'selected' : '' ?>>Perro</option>
                        <option value="Gato"   <?= (isset($_POST['especie']) && $_POST['especie'] == 'Gato')   ? 'selected' : '' ?>>Gato</option>
                        <option value="Conejo" <?= (isset($_POST['especie']) && $_POST['especie'] == 'Conejo') ? 'selected' : '' ?>>Conejo</option>
                        <option value="Ave"    <?= (isset($_POST['especie']) && $_POST['especie'] == 'Ave')    ? 'selected' : '' ?>>Ave</option>
                        <option value="Otro"   <?= (isset($_POST['especie']) && $_POST['especie'] == 'Otro')   ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
            </div>

            <div class="fila-dos">
                <div class="form-grupo">
                    <label>Raza</label>
                    <input type="text" name="raza" 
                           value="<?= isset($_POST['raza']) ? limpiar($_POST['raza']) : '' ?>">
                </div>
                <div class="form-grupo">
                    <label>Edad (en meses)</label>
                    <input type="number" name="edad" min="0" 
                           value="<?= isset($_POST['edad']) ? (int)$_POST['edad'] : '' ?>">
                </div>
            </div>

            <div class="fila-dos">
                <div class="form-grupo">
                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="desconocido">Desconocido</option>
                        <option value="macho"  <?= (isset($_POST['sexo']) && $_POST['sexo'] == 'macho')  ? 'selected' : '' ?>>Macho</option>
                        <option value="hembra" <?= (isset($_POST['sexo']) && $_POST['sexo'] == 'hembra') ? 'selected' : '' ?>>Hembra</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label>Fecha de entrada *</label>
                    <input type="date" name="fecha_entrada" required
                           value="<?= isset($_POST['fecha_entrada']) ? $_POST['fecha_entrada'] : date('Y-m-d') ?>">
                </div>
            </div>

            <div class="form-grupo">
                <label>Estado de salud</label>
                <input type="text" name="estado_salud" 
                       value="<?= isset($_POST['estado_salud']) ? limpiar($_POST['estado_salud']) : '' ?>">
            </div>

            <div class="form-grupo">
                <label>Descripción</label>
                <textarea name="descripcion" placeholder="Describe al animal..."><?= isset($_POST['descripcion']) ? limpiar($_POST['descripcion']) : '' ?></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn_verde">Guardar animal</button>
                <a href="../index.php" class="btn btn_outline">Cancelar</a>
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
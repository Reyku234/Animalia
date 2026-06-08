<?php
session_start();
include 'controladores/conexion.php';
include 'controladores/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animalia - Refugio de animales</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'controladores/nav.php'; ?>


<div class="index">
    <h1>Encuentra tu compañero ideal</h1>
    <a href="#animales" class="btn btn-tierra">Ver animales</a>
</div>

<div class="contenedor" id="animales">

    <form method="GET" action="index.php">
        <div class="filtros" style="margin-top: 30px;">
            <div class="form-grupo">
                <label>Especie</label>
                <select name="especie">
                    <option value="">Todas</option>
                    <option value="Perro"   <?= (isset($_GET['especie']) && $_GET['especie'] == 'Perro')   ? 'selected' : '' ?>>Perro</option>
                    <option value="Gato"    <?= (isset($_GET['especie']) && $_GET['especie'] == 'Gato')    ? 'selected' : '' ?>>Gato</option>
                    <option value="Conejo"  <?= (isset($_GET['especie']) && $_GET['especie'] == 'Conejo')  ? 'selected' : '' ?>>Conejo</option>
                    <option value="Ave"     <?= (isset($_GET['especie']) && $_GET['especie'] == 'Ave')     ? 'selected' : '' ?>>Ave</option>
                    <option value="Otro"    <?= (isset($_GET['especie']) && $_GET['especie'] == 'Otro')    ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
            <div class="form-grupo">
                <label>Sexo</label>
                <select name="sexo">
                    <option value="">Todos</option>
                    <option value="macho"  <?= (isset($_GET['sexo']) && $_GET['sexo'] == 'macho')  ? 'selected' : '' ?>>Macho</option>
                    <option value="hembra" <?= (isset($_GET['sexo']) && $_GET['sexo'] == 'hembra') ? 'selected' : '' ?>>Hembra</option>
                </select>
            </div>
            <div class="form-grupo">
                <label>Estado</label>
                <select name="estado">
                    <option value="disponible" <?= (!isset($_GET['estado']) || $_GET['estado'] == 'disponible') ? 'selected' : '' ?>>Disponibles</option>
                    <option value=""           <?= (isset($_GET['estado']) && $_GET['estado'] == '')             ? 'selected' : '' ?>>Todos</option>
                    <option value="reservado"  <?= (isset($_GET['estado']) && $_GET['estado'] == 'reservado')   ? 'selected' : '' ?>>Reservados</option>
                    <option value="adoptado"   <?= (isset($_GET['estado']) && $_GET['estado'] == 'adoptado')    ? 'selected' : '' ?>>Adoptados</option>
                </select>
            </div>
            <button type="submit" class="btn btn-verde">Buscar</button>
            <a href="index.php" class="btn btn-outline">Limpiar</a>

            <?php if (logeado() && rol_usuario('administrador')): ?>
                
            <div  class="form-grupo">
                <a href="paginas/admin_animal.php" class="btn btn-verde">
                    ➕ Nuevo animal
                </a>
            </div>
            <?php endif; ?>
        </div>
    </form>

    <?php
    // Construir la consulta con los filtros
    $condiciones = array("1=1");
    $tipos = "";
    $valores = array();

    if (!empty($_GET['especie'])) {
        $condiciones[] = "especie = ?";
        $tipos .= "s";
        $valores[] = $_GET['especie'];
    }

    if (!empty($_GET['sexo'])) {
        $condiciones[] = "sexo = ?";
        $tipos .= "s";
        $valores[] = $_GET['sexo'];
    }

    // Estado por defecto = disponible
    $estado = isset($_GET['estado']) ? $_GET['estado'] : 'disponible';
    if ($estado != '') {
        $condiciones[] = "estado_adopcion = ?";
        $tipos .= "s";
        $valores[] = $estado;
    }

    $sql = "SELECT * FROM animales WHERE " . implode(" AND ", $condiciones) . " ORDER BY fecha_registro DESC";

    $stmt = $conexion->prepare($sql);

    if (!empty($valores)) {
        $stmt->bind_param($tipos, ...$valores);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $total = $resultado->num_rows;
    ?>

    <p style="font-size: 0.85rem; color: var(--texto-gris); margin-bottom: 10px;">
        <?php if ($total > 0): ?>
            <?= $total ?> <?= $total == 1 ? 'animal encontrado' : 'animales encontrados' ?>
        <?php endif; ?>
    </p>

    <!-- Grid de animales -->
    <div class="grid-animales">
        <?php if ($total == 0): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--texto-gris);">
                <div style="font-size: 3rem; margin-bottom: 15px;">🔍</div>
                <h3>No se encontraron animales</h3>
                <p>Prueba con otros filtros</p>
            </div>
        <?php else: ?>
            <?php while ($animal = $resultado->fetch_assoc()): ?>
                <a href="paginas/detalle_animal.php?id=<?= $animal['id_animal'] ?>" class="tarjeta-animal">
                    <div class="tarjeta-foto">
                        <?= icono_especie($animal['especie']) ?>
                    </div>
                    <div class="tarjeta-cuerpo">
                        <div class="tarjeta-nombre"><?= limpiar($animal['nombre']) ?></div>
                        <div class="tarjeta-meta">
                            🐾 <?= limpiar($animal['especie']) ?>
                            <?= $animal['raza'] ? ' · ' . limpiar($animal['raza']) : '' ?>
                            &nbsp;|&nbsp; ⏳ <?= edad($animal['edad']) ?>
                        </div>
                        <p class="tarjeta-desc">
                            <?= $animal['descripcion'] ? limpiar($animal['descripcion']) : 'Sin descripción disponible.' ?>
                        </p>
                        <div class="tarjeta-pie">
                            <?= badge_estado($animal['estado_adopcion']) ?>
                            <span style="font-size: 0.8rem; color: var(--tierra);">Ver más →</span>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
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
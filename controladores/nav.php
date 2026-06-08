<?php
// Barra de navegacion comun para todas las paginas
?>

<nav>
    <a href="<?= conexion ?>index.php" class="nav-logo">
        Animalia
        <small>Refugio de animales</small>
    </a>

    <ul class="nav-enlaces">
        <li><a href="<?= conexion ?>index.php">Animales</a></li>

        <?php if (logeado()): ?>
            <li><a href="<?= conexion ?>paginas/mis_solicitudes.php">Mis solicitudes</a></li>

            <?php if (rol_usuario('administrador') || rol_usuario('trabajador')): ?>
                <li><a href="<?= conexion ?>paginas/admin_solicitudes.php">Panel admin</a></li>
            <?php endif; ?>

            <li><a href="<?= conexion ?>paginas/cerrar_sesion.php">Cerrar sesión</a></li>

        <?php else: ?>
            <li><a href="<?= conexion ?>paginas/login.php">Entrar</a></li>
            <li><a href="<?= conexion ?>paginas/registro.php" class="btn-registro">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>
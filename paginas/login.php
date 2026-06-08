<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Si ya esta logueado, ir al inicio
if (logeado()) {
    redirigir('../index.php');
}

$error = "";

// Procesar el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email      = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];

    // Buscar el usuario por email
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificar la contrasena
        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Guardar datos en la sesion
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['rol']        = $usuario['rol'];

            // Redirigir al inicio
            redirigir('../index.php');
        } else {
            $error = "El email o la contraseña son incorrectos.";
        }
    } else {
        $error = "El email o la contraseña son incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="pagina-centrada">
    <div class="caja-form">

        <div class="caja-titulo">
            <div class="icono">🐾</div>
            <h1>Bienvenido</h1>
            <p>Inicia sesión para gestionar tus solicitudes</p>
        </div>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-grupo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" placeholder="tu@email.es"
                       value="<?= isset($_POST['email']) ? limpiar($_POST['email']) : '' ?>"
                       required autofocus>
            </div>
            <div class="form-grupo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-verde" style="width: 100%; justify-content: center; padding: 12px;">
                Iniciar sesión
            </button>
        </form>

        

        <div class="caja-pie">
            ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
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
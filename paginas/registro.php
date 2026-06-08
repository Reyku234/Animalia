<?php
session_start();
include '../controladores/conexion.php';
include '../controladores/funciones.php';

// Si ya esta logueado, ir al inicio
if (logeado()) {
    redirigir('../index.php');
}

$error  = "";
$exito  = false;

// Procesar el formulario cuando se envia
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre     = limpiar($_POST['nombre']);
    $apellidos  = limpiar($_POST['apellidos']);
    $email      = trim(strtolower($_POST['email']));
    $telefono   = limpiar($_POST['telefono']);
    $contrasena = $_POST['contrasena'];
    $confirmar  = $_POST['confirmar'];

    // Validaciones basicas
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($contrasena)) {
        $error = "Por favor, rellena todos los campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido.";
    } elseif (strlen($contrasena) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($contrasena != $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Comprobar que el email no esta ya registrado
        $stmtCheck = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();

        if ($stmtCheck->get_result()->num_rows > 0) {
            $error = "Este email ya está registrado. Prueba a iniciar sesión.";
        } else {
            // Encriptar la contrasena y guardar el usuario
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);

            $stmtInsert = $conexion->prepare(
                "INSERT INTO usuarios (nombre, apellidos, email, contrasena, telefono, rol)
                 VALUES (?, ?, ?, ?, ?, 'adoptante')"
            );
            $stmtInsert->bind_param("sssss", $nombre, $apellidos, $email, $hash, $telefono);

            if ($stmtInsert->execute()) {
                $exito = true;
            } else {
                $error = "Error al crear la cuenta. Inténtalo de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Animalia</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<?php include '../controladores/nav.php'; ?>

<div class="pagina-centrada">
    <div class="caja-form caja-form-grande">

        <?php if ($exito): ?>
            <!-- Mensaje de registro exitoso -->
            <div class="caja-titulo">
                <div class="icono">🎉</div>
                <h1>¡Cuenta creada!</h1>
                <p>Ya puedes iniciar sesión y comenzar el proceso de adopción.</p>
            </div>
            <a href="login.php" class="btn btn-verde" style="width: 100%; text-align: center; display: block; padding: 12px;">
                Ir a iniciar sesión
            </a>

        <?php else: ?>
            <!-- Formulario de registro -->
            <div class="caja-titulo">
                <div class="icono">🏡</div>
                <h1>Crea tu cuenta</h1>
                <p>Únete y empieza el proceso de adopción</p>
            </div>

            <?php if ($error): ?>
                <div class="alerta alerta-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="registro.php">
                <div class="fila-dos">
                    <div class="form-grupo">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ana"
                               value="<?= isset($_POST['nombre']) ? limpiar($_POST['nombre']) : '' ?>"
                               required>
                    </div>
                    <div class="form-grupo">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" id="apellidos" name="apellidos" placeholder="García López"
                               value="<?= isset($_POST['apellidos']) ? limpiar($_POST['apellidos']) : '' ?>"
                               required>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" placeholder="ana@email.es"
                           value="<?= isset($_POST['email']) ? limpiar($_POST['email']) : '' ?>"
                           required>
                </div>

                <div class="form-grupo">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="600 123 456"
                           value="<?= isset($_POST['telefono']) ? limpiar($_POST['telefono']) : '' ?>">
                </div>

                <div class="form-grupo">
                    <label for="contrasena">Contraseña * (mínimo 8 caracteres)</label>
                    <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required minlength="8">
                </div>

                <div class="form-grupo">
                    <label for="confirmar">Confirmar contraseña *</label>
                    <input type="password" id="confirmar" name="confirmar" placeholder="Repite tu contraseña" required>
                </div>

                <button type="submit" class="btn btn-verde" style="width: 100%; text-align: center; padding: 12px;">
                    Crear cuenta
                </button>
            </form>

            <div class="caja-pie">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
            </div>

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
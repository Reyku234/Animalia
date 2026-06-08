<?php
// Funciones de ayuda para la aplicacion

function icono_especie($especie) {
    $especie = strtolower($especie);
    if ($especie == "perro")  return "🐕";
    if ($especie == "gato")   return "🐈";
    if ($especie == "conejo") return "🐇";
    if ($especie == "ave")    return "🦜";
    if ($especie == "pez")    return "🐟";
    return "🐾";
}

// Convierte los meses de la edad en años, porque desde la base de datos viene en meses.
function edad($meses) {
    if ($meses == null || $meses == 0) return "Edad desconocida";
    if ($meses < 12) return $meses . " " . ($meses == 1 ? "mes" : "meses");
    $anios = floor($meses / 12);
    return $anios . " " . ($anios == 1 ? "año" : "años");
}

// Etiquetas de los estados de solicitudes y adopción.
function badge_estado($estado) {
    $textos = array(
        "disponible"   => "Disponible",
        "reservado"    => "Reservado",
        "adoptado"     => "Adoptado",
        "en_tratamiento" => "En tratamiento",
        "pendiente"    => "Pendiente",
        "en_revision"  => "En revisión",
        "aprobada"     => "Aprobada",
        "rechazada"    => "Rechazada",
        "finalizada"   => "Finalizada"
    );

    $clases = array(
        "disponible"   => "badge-disponible",
        "reservado"    => "badge-reservado",
        "adoptado"     => "badge-adoptado",
        "en_tratamiento" => "badge-tratamiento",
        "pendiente"    => "badge-pendiente",
        "en_revision"  => "badge-en_revision",
        "aprobada"     => "badge-aprobada",
        "rechazada"    => "badge-rechazada",
        "finalizada"   => "badge-finalizada"
    );

    $texto = isset($textos[$estado]) ? $textos[$estado] : $estado;
    $clase = isset($clases[$estado]) ? $clases[$estado] : "";

    return '<span class="badge ' . $clase . '">' . $texto . '</span>';
}

// Comprueba si se ha iniciado sesion
function logeado() {
    return isset($_SESSION['id_usuario']);
}

// Checkea el rol 
function rol_usuario($rol) {
    return isset($_SESSION['rol']) && $_SESSION['rol'] == $rol;
}


function redirigir($pagina) {
    header("Location: " . $pagina);
    exit;
}


function limpiar($texto) {
    return htmlspecialchars(strip_tags(trim($texto)));
}
?>

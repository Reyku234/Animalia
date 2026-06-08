
// Mostrar u ocultar la contraseña con el ojito
function togglePassword(inputId, boton) {
    var input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        boton.textContent = "🙈";
    } else {
        input.type = "password";
        boton.textContent = "👁️";
    }
}


// Avisa si las contraseñas coinciden mientras escribes
function validarContrasenas() {
    var contrasena = document.getElementById("contrasena");
    var confirmar  = document.getElementById("confirmar");
    var aviso      = document.getElementById("aviso-contrasena");

    if (!contrasena || !confirmar || !aviso) return;

    if (confirmar.value.length === 0) {
        aviso.textContent = "";
        return;
    }

    if (contrasena.value === confirmar.value) {
        aviso.textContent = "✅ Las contraseñas coinciden";
        aviso.style.color = "#155724";
    } else {
        aviso.textContent = "❌ Las contraseñas no coinciden";
        aviso.style.color = "#721C24";
    }
}


// Avisa si la contraseña tiene al menos 8 caracteres
function validarLongitud() {
    var contrasena = document.getElementById("contrasena");
    var aviso      = document.getElementById("aviso-longitud");

    if (!contrasena || !aviso) return;

    if (contrasena.value.length === 0) {
        aviso.textContent = "";
        return;
    }

    if (contrasena.value.length >= 8) {
        aviso.textContent = "✅ Longitud correcta";
        aviso.style.color = "#155724";
    } else {
        aviso.textContent = "❌ Mínimo 8 caracteres (" + contrasena.value.length + "/8)";
        aviso.style.color = "#721C24";
    }
}


// Valida el formulario de registro antes de enviarlo
function validarFormularioRegistro(event) {
    var contrasena = document.getElementById("contrasena").value;
    var confirmar  = document.getElementById("confirmar").value;

    if (contrasena.length < 8) {
        event.preventDefault();
        alert("La contraseña debe tener al menos 8 caracteres.");
        return false;
    }

    if (contrasena !== confirmar) {
        event.preventDefault();
        alert("Las contraseñas no coinciden.");
        return false;
    }
}


// Abre el modal para confirmar cancelar una solicitud
function mostrarModalCancelar(idSolicitud) {
    var modal  = document.getElementById("modal-cancelar");
    var enlace = document.getElementById("enlace-cancelar");

    if (!modal || !enlace) return;

    enlace.href = "mis-solicitudes.php?cancelar=" + idSolicitud;
    modal.classList.add("activo");
}

function cerrarModalCancelar() {
    document.getElementById("modal-cancelar").classList.remove("activo");
}


// Filtra las tarjetas de animales por nombre en tiempo real
function buscarAnimal() {
    var texto   = document.getElementById("buscador").value.toLowerCase();
    var tarjetas = document.querySelectorAll(".tarjeta_animal");
    var contador = 0;

    tarjetas.forEach(function(tarjeta) {
        var nombre = tarjeta.querySelector(".tarjeta_nombre").textContent.toLowerCase();

        if (nombre.includes(texto)) {
            tarjeta.style.display = "block";
            contador++;
        } else {
            tarjeta.style.display = "none";
        }
    });

    var contadorEl = document.getElementById("contador-animales");
    if (contadorEl) {
        contadorEl.textContent = texto === "" ? "" : contador + (contador === 1 ? " animal encontrado" : " animales encontrados");
    }
}


// Abre el modal del panel admin con los datos de la solicitud
function abrirModal(id, estado, animal, adoptante, comentario) {
    document.getElementById("id_solicitud_input").value  = id;
    document.getElementById("nuevo_estado_select").value = estado;
    document.getElementById("comentarios_input").value   = comentario || "";
    document.getElementById("info-modal").innerHTML      = "<strong>Animal:</strong> " + animal + " &nbsp;|&nbsp; <strong>Adoptante:</strong> " + adoptante;
    document.getElementById("modal_fondo").classList.add("activo");
}

function cerrarModal() {
    document.getElementById("modal_fondo").classList.remove("activo");
}


// Al cargar la pagina, inicializar todo
document.addEventListener("DOMContentLoaded", function() {

    // Añadir el ojito a todos los campos de contraseña
    document.querySelectorAll(".campo_password").forEach(function(campo) {
        var input = campo.querySelector("input");
        var boton = document.createElement("button");
        boton.type = "button";
        boton.textContent = "👁️";
        boton.title = "Mostrar/ocultar contraseña";
        boton.style.cssText = "position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:1rem;";
        boton.onclick = function() { togglePassword(input.id, boton); };
        campo.appendChild(boton);
    });

    // Validacion en tiempo real del formulario de registro
    var inputContrasena = document.getElementById("contrasena");
    var inputConfirmar  = document.getElementById("confirmar");

    if (inputContrasena) inputContrasena.addEventListener("input", function() {
        validarLongitud();
        validarContrasenas();
    });

    if (inputConfirmar) inputConfirmar.addEventListener("input", validarContrasenas);

    // Validar al enviar el formulario
    var formRegistro = document.getElementById("form-registro");
    if (formRegistro) formRegistro.addEventListener("submit", validarFormularioRegistro);

    // Buscador de animales en tiempo real
    var buscador = document.getElementById("buscador");
    if (buscador) buscador.addEventListener("input", buscarAnimal);

    // Cerrar modales al hacer clic fuera
    var modalFondo = document.getElementById("modal_fondo");
    if (modalFondo) modalFondo.addEventListener("click", function(e) {
        if (e.target === this) cerrarModal();
    });

    var modalCancelar = document.getElementById("modal-cancelar");
    if (modalCancelar) modalCancelar.addEventListener("click", function(e) {
        if (e.target === this) cerrarModalCancelar();
    });

});

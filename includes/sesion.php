<?php
// Arranca la sesión (debe ir SIEMPRE antes de imprimir nada en la página)
session_start();

// ¿Hay alguien logueado ahora mismo?
function estaLogueado() {
    return isset($_SESSION["usuario_id"]);
}

// Protege una página: si no has iniciado sesión, te manda al login
function requerirLogin() {
    if (!estaLogueado()) {
        header("Location: login.php");
        exit;
    }
}

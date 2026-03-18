<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuario_autenticado() {
    if (!revisar_usuario()) {
        header('Location:login.php');
        exit();
    }
}

function revisar_usuario() {
    return isset($_SESSION['usuario']);
}

function tiene_nivel($nivel) {
    return isset($_SESSION['nivel']) && $_SESSION['nivel'] == $nivel;
}

usuario_autenticado();

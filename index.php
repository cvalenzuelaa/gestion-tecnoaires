<?php
require_once('./app/sesiones/session.php');

$session = new Session();
$user = $session->getSession();

if (!isset($user['correo'])) {
    include_once('./app/viewer/login.php');
    exit();
}

// Vistas exclusivas para administrador
$paginasAdmin = ['dashadmin', 'usuarios', 'perfiladmin'];
// Vistas exclusivas para usuario
$paginasUsuario = ['dashboard', 'perfil', 'enviarcorreo'];

if (isset($user['rol']) && $user['rol'] === 'admin') {
    $nav = $_GET['nav'] ?? 'dashadmin';
    if (in_array($nav, $paginasAdmin)) {
        include_once('./app/viewer/' . $nav . '.php');
        exit();
    } else {
    }
} else {
    $nav = $_GET['nav'] ?? 'dashboard';
    if (in_array($nav, $paginasUsuario)) {
        include_once('./app/viewer/' . $nav . '.php');
        exit();
    } else {
        include_once('./app/viewer/error/error404.php');
        exit();
    }
}
include_once('./app/viewer/error/error404.php');
exit();
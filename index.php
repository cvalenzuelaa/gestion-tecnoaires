<?php
require_once('./app/sesiones/session.php');

$session = new Session();
$user = $session->getSession();

// 1. VERIFICACIÓN DE SEGURIDAD (CAMBIO AQUÍ)
// Verificamos si existe la variable 'usuario' en la sesión.
// Si tu base de datos usa 'nombre_usuario', asegúrate de que tu login guarde ese dato en la sesión.
if (!isset($user['usuario']) || empty($user['usuario'])) {
    // Si no hay usuario logueado, lo mandamos al Login
    include_once('./app/viewer/login.php');
    exit();
}

// --------------------------------------------------------------------------
// 2. DEFINICIÓN DE ACCESOS (WHITELISTS)
// --------------------------------------------------------------------------

// A. Vistas del ADMIN
$paginasAdmin = [
    'dashboard_admin', 'usuarios', 'clientes', 'vehiculos', 
    'ordenes', 'cotizaciones', 'informes', 'facturas', 'perfil'
];

// B. Vistas de la SECRETARIA
$paginasSecretaria = [
    'dashboard_recepcion', 'clientes', 'vehiculos', 
    'ordenes', 'cotizaciones', 'facturas', 'perfil'
];

// C. Vistas del TÉCNICO
$paginasTecnico = [
    'mis_trabajos', 'crear_informe', 'historial_vehiculo', 'perfil'
];

// --------------------------------------------------------------------------
// 3. LÓGICA DE ENRUTAMIENTO
// --------------------------------------------------------------------------

$nav = $_GET['nav'] ?? '';
$rol = $user['rol']; // Asegúrate que tu Login también guarde el 'rol' en la sesión

switch ($rol) {
    case 'admin':
        if ($nav === '') $nav = 'dashboard_admin'; // Página de inicio del Admin
        
        if (in_array($nav, $paginasAdmin)) {
            include_once('./app/viewer/' . $nav . '.php');
        } else {
            include_once('./app/viewer/error/error404.php');
        }
        break;

    case 'secretaria':
        if ($nav === '') $nav = 'dashboard_recepcion'; // Página de inicio de la Secretaria

        if (in_array($nav, $paginasSecretaria)) {
            include_once('./app/viewer/' . $nav . '.php');
        } else {
            include_once('./app/viewer/error/error404.php');
        }
        break;

    case 'tecnico':
        if ($nav === '') $nav = 'mis_trabajos'; // Página de inicio del Técnico

        if (in_array($nav, $paginasTecnico)) {
            include_once('./app/viewer/' . $nav . '.php');
        } else {
            include_once('./app/viewer/error/error404.php');
        }
        break;

    default:
        // Si el rol es desconocido, cerrar sesión y mandar al login
        include_once('./app/viewer/login.php');
        break;
}

exit();
?>
<?php
class Session {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($idusuario, $usuario, $nombre, $apellido, $rol) {
        $_SESSION['idusuario'] = $idusuario;
        $_SESSION['usuario'] = $usuario;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['rol'] = $rol;

        return [
            'success' => true,
            'message' => 'Sesión iniciada',
            'session' => [
                'idusuario' => $_SESSION['idusuario'],
                'usuario'    => $_SESSION['usuario'],
                'nombre'     => $_SESSION['nombre'],
                'apellido'   => $_SESSION['apellido'],
                'rol'        => $_SESSION['rol'],
            ]
        ];
    }

    public function getSession() {
        return isset($_SESSION['idusuario']) ? [
            'idusuario' => $_SESSION['idusuario'],
            'usuario'=> $_SESSION['usuario'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'rol' => $_SESSION['rol'],
        ] : null;
    }

    public function updateSession($datos) {
        if (isset($datos['nombre'])) $_SESSION['nombre'] = $datos['nombre'];
        if (isset($datos['apellido'])) $_SESSION['apellido'] = $datos['apellido'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
    }

    public function isActive() {
        return isset($_SESSION['idusuario']);
    }
}
?>
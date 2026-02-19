<?php
if (!isset($_POST['nombre']) || !isset($_POST['apellido']) ||!isset($_POST['usuario'])|| !isset($_POST['idusuario']) || !isset($_POST['rol'])) {
  echo json_encode(array("error" => "Datos insuficientes para iniciar sesión."));
  exit;
}

require_once './session.php';

$obj = new Session();
$response = $obj->login($_POST['idusuario'],$_POST['usuario'], $_POST['nombre'], $_POST['apellido'], $_POST['rol']); 
echo json_encode($response);
die();
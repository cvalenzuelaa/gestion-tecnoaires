<?php
require_once __DIR__ . '/../models/clientesModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "No se ha recibido la acción."));
    exit;
}

$obj = new ClientesController();

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;
    case 'getById':
        echo json_encode($obj->getById($_POST['id'] ?? null));
        break;
    case 'buscarPorNombre':
        echo json_encode($obj->buscarPorNombre($_POST['nombre'] ?? null));
        break;
    case 'buscarPorRut':
        echo json_encode($obj->buscarPorRut($_POST['rut'] ?? null));
        break;
    case 'insert':
        echo json_encode($obj->insert($_POST));
        break;
    case 'update':
        echo json_encode($obj->update($_POST));
        break;
    case 'softDelete':
        echo json_encode($obj->softDelete($_POST['id'] ?? null));
        break;
    default:
        echo json_encode(array("error" => "Acción no encontrada."));
        break;
}

class ClientesController
{
    private $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClientesModel();
    }

    public function getAll() { return $this->clienteModel->getAll(); }
    
    public function getById($id) {
        if ($id === null) return ["error" => "ID no proporcionado."];
        return $this->clienteModel->getById($id);
    }

    public function buscarPorNombre($nombre) {
        if (empty($nombre)) return ["error" => "Nombre no proporcionado."];
        return $this->clienteModel->buscarPorNombre($nombre);
    }

    public function buscarPorRut($rut) {
        if (empty($rut)) return ["error" => "RUT no proporcionado."];
        return $this->clienteModel->buscarPorRut($rut);
    }

    public function insert($arr) {
        if (empty($arr['nombre']) || empty($arr['email']) || empty($arr['rut'])) {
            return ["error" => "Faltan campos obligatorios."];
        }
        return $this->clienteModel->insert($arr);
    }

    public function update($arr) {
        if (empty($arr['idcliente'])) return ["error" => "ID no proporcionado."];
        return $this->clienteModel->update($arr);
    }

    public function softDelete($id) {
        if ($id === null) return ["error" => "ID no proporcionado."];
        return $this->clienteModel->softDelete($id);
    }
}
?>

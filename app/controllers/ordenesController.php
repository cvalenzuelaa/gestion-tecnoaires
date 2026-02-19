<?php
require_once __DIR__ . '/../models/ordenesModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "No se ha recibido la acción."));
    exit;
}

$obj = new OrdenesController();

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;
    case 'getById':
        echo json_encode($obj->getById($_POST['id'] ?? null));
        break;
    case 'insert':
        echo json_encode($obj->insert($_POST));
        break;
    case 'softDelete':
        echo json_encode($obj->softDelete($_POST['id'] ?? null));
        break;
    default:
        echo json_encode(array("error" => "Acción no encontrada."));
        break;
}

class OrdenesController
{
    private $model;

    public function __construct()
    {
        $this->model = new OrdenesModel();
    }

    public function getAll() { return $this->model->getAll(); }
    
    public function getById($id) { 
        if ($id === null) return ["error" => "ID no proporcionado."];
        return $this->model->getById($id); 
    }

    public function insert($arr) {
        // Validaciones básicas
        if (empty($arr['idvehiculo']) || empty($arr['solicitud_cliente'])) {
            return ["error" => "Faltan datos obligatorios (Vehículo o Solicitud)."];
        }
        
        // Generar Folio automático si no viene (Ej: OS-YYYYMMDD-RAND)
        if (empty($arr['folio'])) {
            $arr['folio'] = 'OS-' . date('Ymd') . '-' . rand(1000, 9999);
        }

        // Estado por defecto
        if (empty($arr['estado'])) {
            $arr['estado'] = 'ingresado';
        }

        return $this->model->insert($arr);
    }

    public function softDelete($id) { 
        if ($id === null) return ["error" => "ID no proporcionado."];
        return $this->model->softDelete($id); 
    }
}
?>
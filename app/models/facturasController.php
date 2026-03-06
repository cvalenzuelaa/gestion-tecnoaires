<?php
require_once __DIR__ . '/../models/facturasModel.php';

$accion = $_POST['accion'] ?? null;
$controller = new FacturasController();

if (!$accion) {
    echo json_encode(["error" => "Acción no especificada"]);
    exit;
}

switch ($accion) {
    case 'getAll':
        echo json_encode($controller->getAll());
        break;
    case 'getById':
        echo json_encode($controller->getById($_POST['id'] ?? null));
        break;
    case 'guardar':
        echo json_encode($controller->guardar($_POST, $_FILES));
        break;
    case 'cambiarEstado':
        echo json_encode($controller->cambiarEstado($_POST['id'] ?? null, $_POST['estado'] ?? null));
        break;
    case 'eliminar':
        echo json_encode($controller->eliminar($_POST['id'] ?? null));
        break;
    default:
        echo json_encode(["error" => "Acción no válida"]);
        break;
}

class FacturasController
{
    private $model;

    public function __construct() {
        $this->model = new FacturasModel();
    }

    public function getAll() { return $this->model->getAll(); }
    public function getById($id) { return $this->model->getById($id); }
    public function cambiarEstado($id, $estado) { return $this->model->cambiarEstado($id, $estado); }
    public function eliminar($id) { return $this->model->softDelete($id); }

    public function guardar($datos, $archivos) {
        // Manejo básico de archivo (si se sube uno nuevo)
        // Aquí podrías agregar lógica para mover el archivo a /assets/docs/facturas/
        // Por ahora pasamos la ruta si existe
        
        if (!empty($datos['idfactura'])) {
            return $this->model->update($datos);
        } else {
            return $this->model->insert($datos);
        }
    }
}
?>
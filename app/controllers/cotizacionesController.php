<?php
require_once __DIR__ . '/../models/cotizacionesModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "No se ha recibido la acción."));
    exit;
}

$obj = new CotizacionesController();

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

class CotizacionesController
{
    private $model;

    public function __construct()
    {
        $this->model = new CotizacionesModel();
    }

    public function getAll() { return $this->model->getAll(); }
    public function getById($id) { return $this->model->getById($id); }
    public function insert($arr) { return $this->model->insert($arr); }
    public function softDelete($id) { return $this->model->softDelete($id); }
}
?>

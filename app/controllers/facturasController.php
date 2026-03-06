<?php
require_once __DIR__ . '/../models/facturasModel.php';

$accion = $_POST['accion'] ?? null;
if (!$accion) {
    echo json_encode(["error" => "Acción no especificada"]);
    exit;
}

$controller = new FacturasController();

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
    case 'eliminar':
        echo json_encode($controller->eliminar($_POST['id'] ?? null));
        break;
    case 'cambiarEstado':
        echo json_encode($controller->cambiarEstado($_POST));
        break;
    case 'checkNotificaciones':
        echo json_encode($controller->checkNotificaciones());
        break;
    default:
        echo json_encode(["error" => "Acción inválida"]);
        break;
}

class FacturasController
{
    private $model;

    public function __construct()
    {
        $this->model = new FacturasModel();
    }

    public function getAll() { return $this->model->getAll(); }
    public function getById($id) { return $this->model->getById($id); }
    public function eliminar($id) { return $this->model->softDelete($id); }

    public function checkNotificaciones() {
        $facturas = $this->model->getPorVencer();
        return [
            'cantidad' => count($facturas),
            'detalles' => $facturas
        ];
    }

    public function guardar($datos, $archivos)
    {
        if (empty($datos['idcliente']) || empty($datos['folio_sii']) || empty($datos['monto'])) {
            return ["error" => "Faltan datos obligatorios."];
        }

        if (empty($datos['idfactura']) && $this->model->existeFolio($datos['folio_sii'])) {
            return ["error" => "El folio ya existe en el sistema."];
        }

        // Manejo del archivo
        $rutaArchivo = null;
        if (isset($archivos['archivo']) && $archivos['archivo']['error'] === UPLOAD_ERR_OK) {
            $nombreOriginal = $archivos['archivo']['name'];
            $ext = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreFinal = 'FAC_' . $datos['folio_sii'] . '.' . $ext;
            $directorio = __DIR__ . '/../../assets/docs/facturas/';
            
            if (!is_dir($directorio)) mkdir($directorio, 0777, true);
            
            if (move_uploaded_file($archivos['archivo']['tmp_name'], $directorio . $nombreFinal)) {
                $rutaArchivo = '/assets/docs/facturas/' . $nombreFinal;
            } else {
                return ["error" => "Error al subir el archivo."];
            }
        }

        // Si es actualización
        if (!empty($datos['idfactura'])) {
            // Nota: Por ahora el update no actualiza el archivo, solo datos
            return $this->model->update($datos);
        } else {
            $datos['ruta_archivo_pdf'] = $rutaArchivo;
            return $this->model->insert($datos);
        }
    }

    public function cambiarEstado($datos)
    {
        $id = $datos['id'] ?? null;
        $estado = $datos['estado'] ?? null;
        
        if (!$id || !in_array($estado, ['pendiente', 'pagada'])) {
            return ["error" => "Datos inválidos."];
        }
        return $this->model->cambiarEstado($id, $estado);
    }
}
?>
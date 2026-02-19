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
    case 'obtenerPorVehiculo':
        echo json_encode($obj->obtenerPorVehiculo($_POST['idvehiculo'] ?? null));
        break;
    case 'obtenerPorCliente':
        echo json_encode($obj->obtenerPorCliente($_POST['idcliente'] ?? null));
        break;
    case 'obtenerConDetalles':
        echo json_encode($obj->obtenerConDetalles($_POST['id'] ?? null));
        break;
    case 'obtenerPorVencer':
        echo json_encode($obj->obtenerPorVencer());
        break;
    case 'obtenerVencidas':
        echo json_encode($obj->obtenerVencidas());
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

class CotizacionesController
{
    private $cotizacionModel;

    public function __construct()
    {
        $this->cotizacionModel = new CotizacionesModel();
    }

    /**
     * Obtener todas las cotizaciones
     */
    public function getAll()
    {
        return $this->cotizacionModel->getAll();
    }

    /**
     * Obtener cotización por ID
     */
    public function getById($id)
    {
        if ($id === null) {
            return ["error" => "ID no proporcionado."];
        }
        $cotizacion = $this->cotizacionModel->getById($id);
        if (isset($cotizacion['error'])) {
            return $cotizacion;
        }
        // Obtener detalles
        $detalles = $this->cotizacionModel->obtenerConDetalles($id);
        $cotizacion['detalles'] = $detalles;
        return $cotizacion;
    }

    /**
     * Obtener cotizaciones por vehículo
     */
    public function obtenerPorVehiculo($idvehiculo)
    {
        if ($idvehiculo === null) {
            return ["error" => "ID vehículo no proporcionado."];
        }
        return $this->cotizacionModel->obtenerPorVehiculo($idvehiculo);
    }

    /**
     * Obtener cotizaciones por cliente
     */
    public function obtenerPorCliente($idcliente)
    {
        if ($idcliente === null) {
            return ["error" => "ID cliente no proporcionado."];
        }
        return $this->cotizacionModel->obtenerPorCliente($idcliente);
    }

    /**
     * Obtener cotización con detalles
     */
    public function obtenerConDetalles($id)
    {
        if ($id === null) {
            return ["error" => "ID no proporcionado."];
        }
        $cotizacion = $this->cotizacionModel->getById($id);
        if (isset($cotizacion['error'])) {
            return $cotizacion;
        }
        $detalles = $this->cotizacionModel->obtenerConDetalles($id);
        $cotizacion['detalles'] = $detalles;
        return $cotizacion;
    }

    /**
     * Obtener cotizaciones por vencer
     */
    public function obtenerPorVencer()
    {
        return $this->cotizacionModel->obtenerPorVencer();
    }

    /**
     * Obtener cotizaciones vencidas
     */
    public function obtenerVencidas()
    {
        return $this->cotizacionModel->obtenerVencidas();
    }

    /**
     * Insertar nueva cotización
     */
    public function insert($arr)
    {
        if (empty($arr['idvehiculo']) || empty($arr['iddiseniador']) || empty($arr['fecha_emision']) || empty($arr['fecha_vencimiento'])) {
            return ["error" => "Faltan campos obligatorios."];
        }
        $arr['estado'] = $arr['estado'] ?? 'pendiente';
        return $this->cotizacionModel->insert($arr);
    }

    /**
     * Actualizar cotización
     */
    public function update($arr)
    {
        if (empty($arr['idcotizacion'])) {
            return ["error" => "ID de cotización no proporcionado."];
        }
        return $this->cotizacionModel->update($arr);
    }

    /**
     * Eliminar cotización (soft delete)
     */
    public function softDelete($id)
    {
        if ($id === null) {
            return ["error" => "ID no proporcionado."];
        }
        return $this->cotizacionModel->softDelete($id);
    }
}
?>
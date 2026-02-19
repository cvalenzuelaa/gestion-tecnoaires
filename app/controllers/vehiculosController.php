<?php
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../models/clientesModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "No se ha recibido la acción."));
    exit;
}

$obj = new VehiculosController();

switch ($accion) {
    case 'buscar':
        echo json_encode($obj->buscar($_POST));
        break;
    case 'getAll':
        echo json_encode($obj->getAll());
        break;
    case 'getById':
        echo json_encode($obj->getById($_POST['id'] ?? null));
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

class VehiculosController
{
    private $vehiculoModel;
    private $clienteModel;

    public function __construct()
    {
        $this->vehiculoModel = new VehiculosModel();
        $this->clienteModel = new ClientesModel();
    }

    /**
     * Búsqueda unificada por patente, id_equipo, cotización o factura
     */
    public function buscar($arr)
    {
        $patente = $arr['patente'] ?? null;
        $id_equipo = $arr['id_equipo'] ?? null;
        $cotizacion = $arr['cotizacion'] ?? null;
        $factura = $arr['factura'] ?? null;

        $resultado = [];

        // Búsqueda por patente
        if (!empty($patente)) {
            $vehiculos = $this->vehiculoModel->buscarPorPatente($patente);
            if (!isset($vehiculos['error']) && !empty($vehiculos)) {
                foreach ($vehiculos as $vehiculo) {
                    $resultado[] = $this->construirResultado($vehiculo);
                }
                return $resultado;
            }
        }

        // Búsqueda por ID equipo (idvehiculo)
        if (!empty($id_equipo)) {
            $vehiculo = $this->vehiculoModel->getById($id_equipo);
            if (!isset($vehiculo['error']) && $vehiculo) {
                return [$this->construirResultado($vehiculo)];
            }
        }

        // Búsqueda por cotización
        if (!empty($cotizacion)) {
            $vehiculo = $this->buscarPorCotizacion($cotizacion);
            if ($vehiculo) {
                return [$this->construirResultado($vehiculo)];
            }
        }

        // Búsqueda por factura
        if (!empty($factura)) {
            $vehiculo = $this->buscarPorFactura($factura);
            if ($vehiculo) {
                return [$this->construirResultado($vehiculo)];
            }
        }

        return ["error" => "No se encontraron resultados."];
    }

    /**
     * Construir objeto de resultado completo
     */
    private function construirResultado($vehiculo)
    {
        // Obtener datos del cliente
        $cliente = $this->clienteModel->getById($vehiculo['idcliente']);

        // Obtener historial del vehículo
        $historial = $this->vehiculoModel->obtenerHistorial($vehiculo['idvehiculo']);

        return [
            'vehiculo' => $vehiculo,
            'cliente' => $cliente,
            'historial' => $historial
        ];
    }

    /**
     * Buscar vehículo por número de cotización
     */
    private function buscarPorCotizacion($numeroCotizacion)
    {
        try {
            $con = new Conexion();
            $conn = $con->getConexion();
            $sql = "SELECT v.* FROM vehiculos v INNER JOIN cotizaciones c ON v.idvehiculo = c.idvehiculo WHERE c.idcotizacion LIKE :cotizacion LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':cotizacion', "%{$numeroCotizacion}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Buscar vehículo por número de factura
     */
    private function buscarPorFactura($numeroFactura)
    {
        try {
            $con = new Conexion();
            $conn = $con->getConexion();
            $sql = "SELECT v.* FROM vehiculos v INNER JOIN facturas f ON v.idvehiculo = f.idvehiculo WHERE f.idfactura LIKE :factura LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':factura', "%{$numeroFactura}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtener todos los vehículos
     */
    public function getAll()
    {
        return $this->vehiculoModel->getAll();
    }

    /**
     * Obtener vehículo por ID
     */
    public function getById($id)
    {
        if ($id === null) {
            return ["error" => "ID no proporcionado."];
        }
        return $this->vehiculoModel->getById($id);
    }

    /**
     * Insertar nuevo vehículo
     */
    public function insert($arr)
    {
        if (empty($arr['idcliente']) || empty($arr['patente']) || empty($arr['marca']) || empty($arr['modelo'])) {
            return ["error" => "Faltan campos obligatorios."];
        }
        $arr['estado'] = 'activo';
        return $this->vehiculoModel->insert($arr);
    }

    /**
     * Actualizar vehículo
     */
    public function update($arr)
    {
        if (empty($arr['idvehiculo'])) {
            return ["error" => "ID de vehículo no proporcionado."];
        }
        return $this->vehiculoModel->update($arr);
    }

    /**
     * Eliminar vehículo (soft delete)
     */
    public function softDelete($id)
    {
        if ($id === null) {
            return ["error" => "ID no proporcionado."];
        }
        return $this->vehiculoModel->softDelete($id);
    }
}
?>
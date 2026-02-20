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

    public function buscar($arr)
    {
        $patente = isset($arr['patente']) ? trim($arr['patente']) : null;
        $id_equipo = isset($arr['id_equipo']) ? trim($arr['id_equipo']) : null;
        $cotizacion = isset($arr['cotizacion']) ? trim($arr['cotizacion']) : null;
        $factura = isset($arr['factura']) ? trim($arr['factura']) : null;

        $vehiculosEncontrados = [];

        // 1. Búsqueda por patente
        if (!empty($patente)) {
            $vehiculosEncontrados = $this->buscarSQL("SELECT * FROM vehiculos WHERE patente LIKE :val LIMIT 20", $patente);
        }
        // 2. Búsqueda por ID equipo
        elseif (!empty($id_equipo)) {
            $vehiculosEncontrados = $this->buscarSQL("SELECT * FROM vehiculos WHERE idvehiculo LIKE :val LIMIT 20", $id_equipo);
        }
        // 3. Búsqueda por Cotización (Folio) -> Busca Cliente -> Busca Vehículos
        elseif (!empty($cotizacion)) {
            $sql = "SELECT v.* FROM vehiculos v 
                    INNER JOIN clientes cl ON v.idcliente = cl.idcliente 
                    INNER JOIN cotizaciones c ON c.idcliente = cl.idcliente 
                    WHERE c.folio LIKE :val LIMIT 20";
            $vehiculosEncontrados = $this->buscarSQL($sql, $cotizacion);
        }
        // 4. Búsqueda por Factura (Folio SII) -> Busca Cliente -> Busca Vehículos
        elseif (!empty($factura)) {
            $sql = "SELECT v.* FROM vehiculos v 
                    INNER JOIN clientes cl ON v.idcliente = cl.idcliente 
                    INNER JOIN facturas f ON f.idcliente = cl.idcliente 
                    WHERE f.folio_sii LIKE :val LIMIT 20";
            $vehiculosEncontrados = $this->buscarSQL($sql, $factura);
        }

        if (empty($vehiculosEncontrados)) {
            return ["error" => "No se encontraron resultados."];
        }

        // Construir respuesta completa
        $resultado = [];
        foreach ($vehiculosEncontrados as $vehiculo) {
            $resultado[] = $this->construirResultado($vehiculo);
        }
        return $resultado;
    }

    private function buscarSQL($sql, $valor) {
        try {
            $con = new Conexion();
            $conn = $con->getConexion();
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':val', "%{$valor}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function construirResultado($vehiculo)
    {
        $cliente = $this->clienteModel->getById($vehiculo['idcliente']);
        
        $historial = $this->vehiculoModel->obtenerHistorial($vehiculo['idvehiculo']);
        // Si hay error en SQL, devolvemos array vacío para no romper el frontend
        if (isset($historial['error'])) {
            $historial = [];
        }

        return [
            'vehiculo' => $vehiculo,
            'cliente' => $cliente,
            'historial' => $historial
        ];
    }

    public function getAll() { return $this->vehiculoModel->getAll(); }
    public function getById($id) { return $this->vehiculoModel->getById($id); }
    public function insert($arr) { return $this->vehiculoModel->insert($arr); }
    public function update($arr) { return $this->vehiculoModel->update($arr); }
    public function softDelete($id) { return $this->vehiculoModel->softDelete($id); }
}
?>

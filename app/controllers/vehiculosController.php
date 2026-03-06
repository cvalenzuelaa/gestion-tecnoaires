<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../models/clientesModel.php';

use Dompdf\Dompdf;

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
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
    case 'eliminar':
        echo json_encode($obj->softDelete($_POST['id'] ?? null));
        break;
    case 'getByCliente':
        echo json_encode($obj->getByCliente($_POST['idcliente'] ?? null));
        break;
    case 'getWithCliente':
        echo json_encode($obj->getWithCliente($_POST['id'] ?? null));
        break;
    case 'generarReporteHistorial':
        $obj->generarReporteHistorial($_GET['id'] ?? null);
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
            $vehiculosEncontrados = $this->buscarSQL("SELECT * FROM vehiculos WHERE patente LIKE :val AND estado = 1 LIMIT 20", $patente);
        }
        // 2. Búsqueda por ID equipo
        elseif (!empty($id_equipo)) {
            $vehiculosEncontrados = $this->buscarSQL("SELECT * FROM vehiculos WHERE idvehiculo LIKE :val AND estado = 1 LIMIT 20", $id_equipo);
        }
        // 3. Búsqueda por Cotización (Folio) -> Busca Cliente -> Busca Vehículos
        elseif (!empty($cotizacion)) {
            $sql = "SELECT v.* FROM vehiculos v 
                    INNER JOIN clientes cl ON v.idcliente = cl.idcliente 
                    INNER JOIN cotizaciones c ON c.idcliente = cl.idcliente 
                    WHERE c.folio LIKE :val AND v.estado = 1 LIMIT 20";
            $vehiculosEncontrados = $this->buscarSQL($sql, $cotizacion);
        }
        // 4. Búsqueda por Factura (Folio SII) -> Busca Cliente -> Busca Vehículos
        elseif (!empty($factura)) {
            $sql = "SELECT v.* FROM vehiculos v 
                    INNER JOIN clientes cl ON v.idcliente = cl.idcliente 
                    INNER JOIN facturas f ON f.idcliente = cl.idcliente 
                    WHERE f.folio_sii LIKE :val AND v.estado = 1 LIMIT 20";
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

    public function getByCliente($idcliente) {
        if (!$idcliente) return [];
        return $this->vehiculoModel->getByCliente($idcliente);
    }

    public function getWithCliente($id) {
        $vehiculo = $this->vehiculoModel->getById($id);
        if (!$vehiculo) return ["error" => "Vehículo no encontrado"];
        
        $cliente = $this->clienteModel->getById($vehiculo['idcliente']);
        return ["vehiculo" => $vehiculo, "cliente" => $cliente];
    }

    public function generarReporteHistorial($id) {
        if (!$id) die("ID de vehículo no proporcionado.");

        $vehiculo = $this->vehiculoModel->getById($id);
        $cliente = $this->clienteModel->getById($vehiculo['idcliente']);
        $historial = $this->vehiculoModel->obtenerHistorial($id);

        // Separar las órdenes y los informes en arreglos distintos
        $ordenes = [];
        $informes = [];
        
        if (is_array($historial) && !isset($historial['error'])) {
            foreach ($historial as $h) {
                if ($h['tipo'] === 'orden') {
                    $ordenes[] = $h;
                } else if ($h['tipo'] === 'informe') {
                    $informes[] = $h;
                }
            }
        }

        // Construcción del HTML para el PDF
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .info-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
                .info-box td { padding: 5px; border: 1px solid #ddd; }
                .info-title { background-color: #f2f2f2; font-weight: bold; }
                table.historial { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                table.historial th, table.historial td { border: 1px solid #000; padding: 8px; text-align: left; }
                table.historial th { background-color: #e0e0e0; }
                .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #333; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>Historial de Servicios del Vehículo</h2>
                <p>Fecha de emisión: ' . date('d/m/Y H:i') . '</p>
            </div>

            <table class="info-box">
                <tr>
                    <td class="info-title">Cliente / Empresa</td>
                    <td>' . ($cliente['nombre'] ?? '-') . '</td>
                    <td class="info-title">RUT</td>
                    <td>' . ($cliente['rut_empresa'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="info-title">Vehículo</td>
                    <td>' . ($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? '') . '</td>
                    <td class="info-title">Patente</td>
                    <td>' . ($vehiculo['patente'] ?? '-') . '</td>
                </tr>
            </table>';

        // --- TABLA 1: ÓRDENES DE SERVICIO ---
        $html .= '
            <div class="section-title">1. Órdenes de Servicio</div>
            <table class="historial">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>';
        
        if (empty($ordenes)) {
            $html .= '<tr><td colspan="4" style="text-align:center;">No hay órdenes de servicio registradas.</td></tr>';
        } else {
            foreach ($ordenes as $o) {
                $fecha = date('d/m/Y', strtotime($o['fecha']));
                $html .= "<tr>
                            <td>{$fecha}</td>
                            <td>{$o['folio']}</td>
                            <td>{$o['descripcion']}</td>
                          </tr>";
            }
        }
        $html .= '</tbody></table>';

        // --- TABLA 2: INFORMES TÉCNICOS ---
        $html .= '
            <div class="section-title">2. Informes Técnicos</div>
            <table class="historial">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Trabajo Realizado</th>
                    </tr>
                </thead>
                <tbody>';
        
        if (empty($informes)) {
            $html .= '<tr><td colspan="3" style="text-align:center;">No hay informes técnicos registrados.</td></tr>';
        } else {
            foreach ($informes as $i) {
                $fecha = date('d/m/Y', strtotime($i['fecha']));
                $html .= "<tr>
                            <td>{$fecha}</td>
                            <td>{$i['folio']}</td>
                            <td>{$i['descripcion']}</td>
                          </tr>";
            }
        }
        $html .= '</tbody></table>
        </body></html>';

        // Generar PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Historial_{$vehiculo['patente']}.pdf", ["Attachment" => true]);
    }
}
?>

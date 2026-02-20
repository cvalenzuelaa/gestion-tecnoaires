<?php
// Cargar librerías de Composer para Excel
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/cotizacionesModel.php';
require_once __DIR__ . '/../models/clientesModel.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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
    private $clienteModel;

    public function __construct()
    {
        $this->model = new CotizacionesModel();
        $this->clienteModel = new ClientesModel();
    }

    public function getAll() { return $this->model->getAll(); }
    public function getById($id) { return $this->model->getById($id); }
    public function insert($arr) { return $this->model->insert($arr); }
    public function softDelete($id) { return $this->model->softDelete($id); }

    public function insert($arr) {
        try {
            // 1. Obtener datos del cliente
            $cliente = $this->clienteModel->getById($arr['idcliente']);
            if (!$cliente) return ["error" => "Cliente no encontrado"];

            // 2. Procesar detalles (vienen como JSON string desde el JS)
            $detalles = json_decode($arr['detalles'], true);
            
            // 3. Generar Excel
            $nombrePlantilla = 'plantilla_cotizacion.xlsx';
            $rutaPlantilla = __DIR__ . '/../../assets/templates/' . $nombrePlantilla;

            if (!file_exists($rutaPlantilla)) {
                return ["error" => "No se encuentra la plantilla Excel"];
            }

            $spreadsheet = IOFactory::load($rutaPlantilla);
            $sheet = $spreadsheet->getActiveSheet();

            // --- CONFIGURACIÓN DE CELDAS (AJUSTA ESTO A TU PLANTILLA) ---
            $folio = 'COT-' . date('Y') . '-' . rand(1000, 9999);
            
            $sheet->setCellValue('E4', $folio);                 // Celda Folio
            $sheet->setCellValue('E5', date('d/m/Y'));          // Celda Fecha
            $sheet->setCellValue('B8', $cliente['nombre']);     // Celda Nombre Cliente
            $sheet->setCellValue('B9', $cliente['rut_empresa']);// Celda RUT
            $sheet->setCellValue('B10', $cliente['direccion']); // Celda Dirección
            
            // Llenar items (empezando en fila 14 por ejemplo)
            $fila = 14; 
            $totalNeto = 0;

            foreach ($detalles as $item) {
                $sheet->setCellValue('A' . $fila, $item['cantidad']);
                $sheet->setCellValue('B' . $fila, $item['descripcion']);
                $sheet->setCellValue('E' . $fila, $item['precio']);
                
                $totalLinea = $item['cantidad'] * $item['precio'];
                $sheet->setCellValue('F' . $fila, $totalLinea);
                
                $totalNeto += $totalLinea;
                $fila++;
            }

            // Totales (si tu Excel no tiene fórmulas automáticas)
            $iva = $totalNeto * 0.19;
            $totalFinal = $totalNeto + $iva;
            
            // Guardar archivo
            $nombreArchivo = 'Cotizacion_' . $folio . '.xlsx';
            $rutaGuardado = __DIR__ . '/../../assets/docs/cotizaciones/';
            if (!is_dir($rutaGuardado)) mkdir($rutaGuardado, 0777, true);
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($rutaGuardado . $nombreArchivo);

            // 4. Guardar en BD
            $arr['folio'] = $folio;
            $arr['total_neto'] = $totalNeto;
            $arr['iva'] = $iva;
            $arr['total_final'] = $totalFinal;
            $arr['estado'] = 'borrador';
            $arr['ruta_archivo'] = '/assets/docs/cotizaciones/' . $nombreArchivo;
            $arr['idusuario'] = '1'; // ID temporal o tomar de sesión

            $resBD = $this->model->insert($arr);

            if (isset($resBD['success'])) {
                return [
                    "success" => "Cotización creada correctamente",
                    "url_archivo" => $arr['ruta_archivo']
                ];
            } else {
                return $resBD;
            }

        } catch (Exception $e) {
            return ["error" => "Error generando Excel: " . $e->getMessage()];
        }
    }
}
?>

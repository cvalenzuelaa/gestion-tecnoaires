<?php
// Cargar librerías
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/cotizacionesModel.php';
require_once __DIR__ . '/../models/clientesModel.php';
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../sesiones/session.php'; // 1. Importar archivo de sesión

use PhpOffice\PhpSpreadsheet\IOFactory;

// 2. Iniciar sesión para capturar el ID
$session = new Session();
$userSession = $session->getSession();

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
        echo json_encode($obj->insert($_POST, $userSession)); // Pasamos la sesión
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
    private $vehiculoModel;

    public function __construct()
    {
        $this->model = new CotizacionesModel();
        $this->clienteModel = new ClientesModel();
        $this->vehiculoModel = new VehiculosModel();
    }

    public function getAll() { return $this->model->getAll(); }
    public function getById($id) { return $this->model->getById($id); }
    public function softDelete($id) { return $this->model->softDelete($id); }

    public function insert($arr, $userSession) {
        try {
            // Validar que el usuario esté logueado
            if (!$userSession) return ["error" => "Sesión no iniciada"];

            // 1. Obtener datos del cliente
            $cliente = $this->clienteModel->getById($arr['idcliente']);
            if (!$cliente) return ["error" => "Cliente no encontrado"];

            // 1.5 Obtener datos del vehículo
            $vehiculo = $this->vehiculoModel->getById($arr['idvehiculo']);
            if (!$vehiculo) return ["error" => "Vehículo no encontrado"];

            // 2. Procesar detalles
            $detalles = json_decode($arr['detalles'], true);
            
            // 3. Generar Excel
            $nombrePlantilla = 'plantilla_cotizacion.xlsx';
            $rutaPlantilla = __DIR__ . '/../../assets/templates/' . $nombrePlantilla;

            if (!file_exists($rutaPlantilla)) {
                return ["error" => "No se encuentra la plantilla Excel"];
            }

            $spreadsheet = IOFactory::load($rutaPlantilla);
            $sheet = $spreadsheet->getActiveSheet();

            $folio = 'COT-' . date('Y') . '-' . rand(1000, 9999);
            
            $sheet->setCellValue('F5', $folio);
            $sheet->setCellValue('F6', date('d/m/Y'));
            $sheet->setCellValue('B13', $cliente['nombre']);
            $sheet->setCellValue('B11', $cliente['rut_empresa']);
            
            // Nuevas ubicaciones solicitadas
            $sheet->setCellValue('F7', $arr['validez_dias'] . ' días'); // Días de validez
            $sheet->getStyle('F7')->getFont()->setBold(true); // Negrita para la validez
            $sheet->setCellValue('A40', $vehiculo['marca'] . ' ' . $vehiculo['modelo']); // Modelo
            $sheet->setCellValue('E39', $vehiculo['patente']); // Patente
            
            $fila = 16; 
            $totalNeto = 0;
            $currencyFormat = '$ #,##0'; // Formato: $ 1.000.000

            foreach ($detalles as $item) {
                $sheet->setCellValue('A' . $fila, $item['descripcion']);
                $sheet->setCellValue('D' . $fila, $item['cantidad']);
                $sheet->setCellValue('E' . $fila, $item['precio']);
                $totalLinea = $item['cantidad'] * $item['precio'];
                $sheet->setCellValue('F' . $fila, $totalLinea);
                
                // Aplicar formato moneda a las celdas de precio y total
                $sheet->getStyle('E' . $fila)->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle('F' . $fila)->getNumberFormat()->setFormatCode($currencyFormat);
                
                $totalNeto += $totalLinea;
                $fila++;
            }

            $iva = $totalNeto * 0.19;
            $totalFinal = $totalNeto + $iva;

            // Totales en ubicaciones específicas
            $sheet->setCellValue('F33', $totalNeto);
            $sheet->setCellValue('F34', $iva);
            $sheet->setCellValue('F35', $totalFinal);
            
            // Aplicar formato moneda a los totales
            $sheet->getStyle('F33:F35')->getNumberFormat()->setFormatCode($currencyFormat);

            $nombreArchivo = 'Cotizacion_' . $folio . '.xlsx';
            $rutaGuardado = __DIR__ . '/../../assets/docs/cotizaciones/';
            if (!is_dir($rutaGuardado)) mkdir($rutaGuardado, 0777, true);
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($rutaGuardado . $nombreArchivo);

            // 4. Guardar en BD con el ID de la sesión corregido
            $arr['folio'] = $folio;
            $arr['total_neto'] = $totalNeto;
            $arr['iva'] = $iva;
            $arr['total_final'] = $totalFinal;
            $arr['estado'] = 'borrador';
            $arr['ruta_archivo'] = '/assets/docs/cotizaciones/' . $nombreArchivo;
            
            // CORRECCIÓN AQUÍ: Capturamos el id de la sesión
            $arr['idusuario'] = $userSession['idusuario']; 

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
            return ["error" => "Error: " . $e->getMessage()];
        }
    }
}
?>
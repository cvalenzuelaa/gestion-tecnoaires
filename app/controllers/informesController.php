<?php
// Asegúrate de tener el autoload de Composer
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/ordenesModel.php';
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../models/clientesModel.php';
require_once __DIR__ . '/../conexion/conexion.php';

use PhpOffice\PhpWord\TemplateProcessor;

$accion = $_POST['accion'] ?? null;

if ($accion === 'insert') {
    $controller = new InformesController();
    echo json_encode($controller->generarInforme($_POST));
} else {
    echo json_encode(['error' => 'Acción no válida']);
}

class InformesController {
    private $ordenModel;
    private $vehiculoModel;
    private $clienteModel;
    private $conn;

    public function __construct() {
        $this->ordenModel = new OrdenesModel();
        $this->vehiculoModel = new VehiculosModel();
        $this->clienteModel = new ClientesModel();
        $db = new Conexion();
        $this->conn = $db->getConexion();
    }

    public function generarInforme($datos) {
        try {
            // 1. Obtener datos completos
            $idOrden = $datos['idorden'];
            $orden = $this->ordenModel->getById($idOrden);
            if (!$orden) throw new Exception("Orden no encontrada");

            $vehiculo = $this->vehiculoModel->getById($orden['idvehiculo']);
            $cliente = $this->clienteModel->getById($vehiculo['idcliente']);

            // 2. Configurar rutas
            $nombrePlantilla = 'plantilla_informe.docx';
            $rutaPlantilla = __DIR__ . '/../../assets/templates/' . $nombrePlantilla;
            
            if (!file_exists($rutaPlantilla)) {
                throw new Exception("No se encuentra la plantilla en: $rutaPlantilla");
            }

            // 3. Cargar Plantilla Word
            $template = new TemplateProcessor($rutaPlantilla);

            // 4. Reemplazar variables (Asegúrate que tu Word tenga estas variables ${variable})
            $folioInforme = 'INF-' . date('Y') . '-' . rand(100, 999);
            
            $template->setValue('folio', $folioInforme);
            $template->setValue('fecha', date('d-m-Y'));
            $template->setValue('cliente', $cliente['nombre'] . ' ' . $cliente['apellido']);
            $template->setValue('rut_cliente', $cliente['rut_empresa']);
            $template->setValue('vehiculo', $vehiculo['marca'] . ' ' . $vehiculo['modelo']);
            $template->setValue('patente', $vehiculo['patente']);
            $template->setValue('kilometraje', 'N/A'); // Si tuvieras este dato

            // Reemplazo de bloques de texto grandes (preserva saltos de línea)
            $template->setValue('trabajo_realizado', $datos['trabajo_realizado']);
            $template->setValue('repuestos', $datos['repuestos']);
            $template->setValue('observaciones', $datos['observaciones']);

            // 5. Guardar archivo generado
            $nombreArchivo = 'Informe_' . $folioInforme . '.docx';
            $rutaGuardado = __DIR__ . '/../../assets/docs/informes/';
            
            if (!is_dir($rutaGuardado)) mkdir($rutaGuardado, 0777, true);
            
            $template->saveAs($rutaGuardado . $nombreArchivo);

            // 6. Guardar en Base de Datos
            $this->guardarEnBD([
                'idorden' => $idOrden,
                'folio' => $folioInforme,
                'trabajo' => $datos['trabajo_realizado'],
                'repuestos' => $datos['repuestos'],
                'observaciones' => $datos['observaciones'],
                'ruta' => '/assets/docs/informes/' . $nombreArchivo
            ]);

            return [
                'success' => 'Informe generado correctamente',
                'url_archivo' => '/assets/docs/informes/' . $nombreArchivo
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function guardarEnBD($datos) {
        // Lógica simple de inserción directa para este ejemplo
        // Idealmente esto iría en informesModel.php
        $sql = "INSERT INTO informes_tecnicos (idinforme, idorden, folio, fecha_informe, trabajo_realizado, repuestos_usados, observaciones, ruta_archivo) 
                VALUES (UUID_SHORT(), :idorden, :folio, NOW(), :trabajo, :repuestos, :obs, :ruta)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':idorden' => $datos['idorden'],
            ':folio' => $datos['folio'],
            ':trabajo' => $datos['trabajo'],
            ':repuestos' => $datos['repuestos'],
            ':obs' => $datos['observaciones'],
            ':ruta' => $datos['ruta']
        ]);
    }
}
?>
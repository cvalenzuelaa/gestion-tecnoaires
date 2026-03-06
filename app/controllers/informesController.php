<?php
// Asegúrate de tener el autoload de Composer
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/ordenesModel.php';
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../models/clientesModel.php';
require_once __DIR__ . '/../models/informesModel.php'; // 1. Incluir el nuevo modelo

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
    private $informesModel; // 2. Propiedad para el nuevo modelo

    public function __construct() {
        $this->ordenModel = new OrdenesModel();
        $this->vehiculoModel = new VehiculosModel();
        $this->clienteModel = new ClientesModel();
        $this->informesModel = new InformesModel(); // 3. Instanciar el nuevo modelo
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
            
            // Datos del Cliente y Vehículo
            $nombreCliente = !empty($cliente['razon_social']) ? $cliente['razon_social'] : ($cliente['nombre'] . ' ' . $cliente['apellido']);
            $template->setValue('cliente', $nombreCliente);
            $template->setValue('rut_cliente', $cliente['rut_empresa']);
            $template->setValue('vehiculo', $vehiculo['marca'] . ' ' . $vehiculo['modelo']);
            $template->setValue('patente', $vehiculo['patente']);
            $template->setValue('ciudad', $cliente['ciudad'] ?? '');
            $template->setValue('direccion', $cliente['direccion'] ?? ''); // Campo nuevo
            $template->setValue('faena', $datos['faena'] ?? '');

            // Reemplazo de bloques de texto grandes (preserva saltos de línea)
            $template->setValue('observaciones', $datos['observaciones']); // Falla Reportada
            $template->setValue('causa_falla', $datos['causa_falla'] ?? '');
            $template->setValue('trabajo_realizado', $datos['trabajo_realizado']);

            // Procesar Imágenes
            $this->procesarImagen($template, 'foto1', $_FILES['foto1'] ?? null);
            $this->procesarImagen($template, 'foto2', $_FILES['foto2'] ?? null);
            $this->procesarImagen($template, 'foto3', $_FILES['foto3'] ?? null);
            $this->procesarImagen($template, 'foto4', $_FILES['foto4'] ?? null);
            $this->procesarImagen($template, 'foto5', $_FILES['foto5'] ?? null);

            // 5. Guardar archivo generado
            $nombreArchivo = 'Informe_' . $folioInforme . '.docx';
            $rutaGuardado = __DIR__ . '/../../assets/docs/informes/';
            
            if (!is_dir($rutaGuardado)) mkdir($rutaGuardado, 0777, true);
            
            $template->saveAs($rutaGuardado . $nombreArchivo);

            // 6. Guardar en Base de Datos usando el Modelo
            $this->informesModel->insert([
                'idorden' => $idOrden,
                'folio' => $folioInforme,
                'trabajo' => $datos['trabajo_realizado'],
                'observaciones' => "Faena: " . ($datos['faena'] ?? '-') . "\nCausa: " . ($datos['causa_falla'] ?? '-') . "\n\n" . $datos['observaciones'],
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

    private function procesarImagen($template, $varName, $file) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $template->setImageValue($varName, [
                'path' => $file['tmp_name'],
                'width' => 200, 
                'height' => 200,
                'ratio' => true
            ]);
        } else {
            $template->setValue($varName, ''); // Limpiar variable si no hay foto
        }
    }
}
?>
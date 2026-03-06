<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/ordenesModel.php';
require_once __DIR__ . '/../models/vehiculosModel.php';
require_once __DIR__ . '/../models/clientesModel.php';

use PhpOffice\PhpWord\TemplateProcessor;

$accion = $_POST['accion'] ?? null;
if (!$accion) {
    echo json_encode(["error" => "Acción no especificada"]);
    exit;
}

$controller = new OrdenesController();

switch ($accion) {
    case 'getById':
        echo json_encode($controller->getById($_POST['id'] ?? null));
        break;
    case 'getAll':
        echo json_encode($controller->getAll());
        break;
    case 'insert':
        // Esta es la acción simple que ya tenías para el dashboard
        echo json_encode($controller->insertSimple($_POST));
        break;
    case 'generarOrdenPDF':
        echo json_encode($controller->generarOrdenWord($_POST));
        break;
    default:
        echo json_encode(["error" => "Acción no válida"]);
        break;
}

class OrdenesController
{
    private $ordenModel;
    private $vehiculoModel;
    private $clienteModel;

    public function __construct()
    {
        $this->ordenModel = new OrdenesModel();
        $this->vehiculoModel = new VehiculosModel();
        $this->clienteModel = new ClientesModel();
    }

    public function getById($id) { return $this->ordenModel->getById($id); }
    public function getAll() { return $this->ordenModel->getAll(); }

    public function insertSimple($datos) {
        // Lógica para inserción simple desde el dashboard
        // Ahora usamos la secuencia numérica correcta
        $folio = $this->ordenModel->getSiguienteFolio();
        $datos['folio'] = $folio;
        return $this->ordenModel->insert($datos);
    }

    public function generarOrdenWord($datos)
    {
        try {
            // 1. Validar y obtener datos
            $idVehiculo = $datos['idvehiculo'];
            if (!$idVehiculo) throw new Exception("ID de vehículo no proporcionado.");

            $vehiculo = $this->vehiculoModel->getById($idVehiculo);
            if (!$vehiculo) throw new Exception("Vehículo no encontrado.");

            $cliente = $this->clienteModel->getById($vehiculo['idcliente']);
            if (!$cliente) throw new Exception("Cliente no encontrado.");

            $detalles = json_decode($datos['detalles'], true);
            $observaciones = $datos['observaciones'] ?? '';

            // 2. Configurar plantilla y folio
            $nombrePlantilla = 'plantilla_ordendeservicio.docx';
            $rutaPlantilla = __DIR__ . '/../../assets/templates/' . $nombrePlantilla;
            if (!file_exists($rutaPlantilla)) {
                throw new Exception("La plantilla Word '{$nombrePlantilla}' no se encuentra en la carpeta 'assets/templates/'.");
            }
            
            $templateProcessor = new TemplateProcessor($rutaPlantilla);

            // 3. Obtener nuevo número de orden
            $nuevoFolio = $this->ordenModel->getSiguienteFolio();

            // 4. Llenar variables de la plantilla
            $templateProcessor->setValue('numero_orden', $nuevoFolio);
            $templateProcessor->setValue('fecha', date('d/m/Y'));
            $templateProcessor->setValue('empresa', htmlspecialchars($cliente['nombre'] ?: $cliente['nombre']));
            $templateProcessor->setValue('direccion', htmlspecialchars($cliente['direccion']));
            $templateProcessor->setValue('contacto', htmlspecialchars($cliente['telefono'] ?: $cliente['email']));
            $templateProcessor->setValue('modelo', htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']));
            $templateProcessor->setValue('patente', htmlspecialchars($vehiculo['patente']));
            $templateProcessor->setValue('observacion', htmlspecialchars($observaciones));

            // 5. Llenar tabla de servicios (hasta 10 filas)
            $rowCount = 0;
            $total = 0;
            foreach ($detalles as $index => $item) {
                if ($index >= 10) break; // Limitar a 10 items
                $rowCount = $index + 1;
                
                $precio = (float)$item['precio'];
                $cantidad = (float)$item['cantidad'];
                $total += $precio * $cantidad;

                $templateProcessor->setValue("cantidad_{$rowCount}", htmlspecialchars($item['cantidad']));
                $templateProcessor->setValue("descripcion_{$rowCount}", htmlspecialchars($item['descripcion']));
                $templateProcessor->setValue("valor_{$rowCount}", '$' . number_format($precio, 0, ',', '.'));
            }

            // Limpiar las variables restantes en la plantilla
            for ($i = $rowCount + 1; $i <= 10; $i++) {
                $templateProcessor->setValue("cantidad_{$i}", '');
                $templateProcessor->setValue("descripcion_{$i}", '');
                $templateProcessor->setValue("valor_{$i}", '');
            }

            // Asignar el total a la plantilla
            $templateProcessor->setValue('total', '$' . number_format($total, 0, ',', '.'));

            // 6. Guardar el archivo Word generado
            $nombreArchivo = 'OS_' . $nuevoFolio . '.docx';
            $rutaGuardado = __DIR__ . '/../../assets/docs/ordenes/';
            if (!is_dir($rutaGuardado)) {
                mkdir($rutaGuardado, 0777, true);
            }
            $templateProcessor->saveAs($rutaGuardado . $nombreArchivo);
            $rutaRelativa = '/assets/docs/ordenes/' . $nombreArchivo;

            // 7. Guardar en la base de datos
            $datosParaBD = [
                'idvehiculo' => $idVehiculo,
                'folio' => $nuevoFolio,
                'observaciones' => $observaciones,
                'detalles' => $detalles,
                'ruta_archivo' => $rutaRelativa
            ];
            $this->ordenModel->insert($datosParaBD);

            return [
                'success' => 'Orden generada correctamente.',
                'url_archivo' => $rutaRelativa
            ];

        } catch (Exception $e) {
            return ['error' => 'Error en Controller: ' . $e->getMessage()];
        }
    }
}
?>
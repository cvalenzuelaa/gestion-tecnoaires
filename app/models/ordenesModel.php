<?php
require_once __DIR__ . '/../conexion/conexion.php';

class OrdenesModel
{
    private $conn;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE idorden = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error en Model: " . $e->getMessage()];
        }
    }

    public function getAll()
    {
        try {
            $sql = "SELECT os.idorden, os.folio, os.fecha_ingreso, os.estado, v.patente, c.nombre as nombre_cliente
                    FROM ordenes_servicio os
                    JOIN vehiculos v ON os.idvehiculo = v.idvehiculo
                    JOIN clientes c ON v.idcliente = c.idcliente                    ORDER BY os.fecha_ingreso DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Error en Model: " . $e->getMessage()];
        }
    }

    public function getSiguienteFolio()
    {
        try {
            // Busca el folio más alto, lo convierte a número y le suma 1.
            // Filtramos con REGEXP para considerar solo los folios puramente numéricos y evitar conflictos con 'OS-...'
            $sql = "SELECT MAX(CAST(folio AS UNSIGNED)) as max_folio FROM ordenes_servicio WHERE folio REGEXP '^[0-9]+$'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $max_folio = $stmt->fetchColumn();
            
            // Si no hay ordenes, empieza en 502. Si hay, suma 1 al máximo.
            $siguienteNumero = ($max_folio === null || $max_folio < 502) ? 502 : $max_folio + 1;
            
            return str_pad($siguienteNumero, 7, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            // Fallback en caso de error, genera un número aleatorio.
            return str_pad(rand(1000, 9999), 7, '0', STR_PAD_LEFT);
        }
    }

    public function insert($datos)
    {
        $this->conn->beginTransaction();
        try {
            $sqlUniq = "SELECT UUID_SHORT() as id";
            $stmtUniq = $this->conn->query($sqlUniq);
            $idOrden = $stmtUniq->fetchColumn();

            // Ajustado a la estructura real de la base de datos (sin observaciones, sin ruta_archivo)
            $sql = "INSERT INTO ordenes_servicio (idorden, idvehiculo, folio, solicitud_cliente, estado) 
                    VALUES (:idorden, :idvehiculo, :folio, :solicitud, :estado)";
            
            // Mapear observaciones a solicitud_cliente si es necesario
            $solicitud = $datos['solicitud_cliente'] ?? ($datos['observaciones'] ?? '');

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':idorden' => $idOrden,
                ':idvehiculo' => $datos['idvehiculo'],
                ':folio' => $datos['folio'],
                ':solicitud' => $solicitud,
                ':estado' => $datos['estado'] ?? 'ingresado'
            ]);

            // Nota: Se omitió la inserción en 'orden_detalles' porque la tabla no existe en el esquema proporcionado.
            
            $this->conn->commit();
            return ["success" => "Orden guardada.", "id" => $idOrden];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["error" => "Error en Model->insert: " . $e->getMessage()];
        }
    }
}
?>
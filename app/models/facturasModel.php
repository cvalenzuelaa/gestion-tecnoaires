<?php
require_once __DIR__ . '/../conexion/conexion.php';

class FacturasModel
{
    private $conn;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getAll()
    {
        try {
            $sql = "SELECT f.*, c.nombre as nombre_cliente, c.rut_empresa 
                    FROM facturas f 
                    JOIN clientes c ON f.idcliente = c.idcliente 
                    WHERE f.estado = 1
                    ORDER BY f.fecha_vencimiento ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::getAll: " . $e->getMessage());
            return ["error" => "Error al cargar las facturas."];
        }
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM facturas WHERE idfactura = :id AND estado = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::getById: " . $e->getMessage());
            return ["error" => "Error al obtener la factura."];
        }
    }

    // Obtiene facturas pendientes que vencen en los próximos 7 días o ya vencieron
    public function getPorVencer()
    {
        try {
            $sql = "SELECT f.folio_sii, f.fecha_vencimiento, c.nombre as nombre_cliente,
                    DATEDIFF(f.fecha_vencimiento, CURDATE()) as dias_restantes
                    FROM facturas f
                    JOIN clientes c ON f.idcliente = c.idcliente
                    WHERE f.estado_pago = 'pendiente' 
                    AND f.estado = 1
                    AND f.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY f.fecha_vencimiento ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::getPorVencer: " . $e->getMessage());
            return [];
        }
    }

    public function insert($datos)
    {
        try {
            // Calcular fecha de vencimiento (30 días después de la emisión)
            $fechaEmision = new DateTime($datos['fecha_emision']);
            $fechaVencimiento = clone $fechaEmision;
            $fechaVencimiento->modify('+30 days');

            $sql = "INSERT INTO facturas (idfactura, idcliente, folio_sii, fecha_emision, fecha_vencimiento, monto, estado_pago, ruta_archivo_pdf, estado) 
                    VALUES (UUID_SHORT(), :idcliente, :folio_sii, :fecha_emision, :fecha_vencimiento, :monto, 'pendiente', :ruta_archivo_pdf, 1)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':idcliente' => $datos['idcliente'],
                ':folio_sii' => $datos['folio_sii'],
                ':fecha_emision' => $datos['fecha_emision'],
                ':fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                ':monto' => $datos['monto'],
                // Se usa el nombre de la columna y se asegura que sea nulo si no viene
                ':ruta_archivo_pdf' => $datos['ruta_archivo_pdf'] ?? null
            ]);

            return ($stmt->rowCount() > 0) ? ["success" => "Factura registrada correctamente."] : ["error" => "No se pudo registrar la factura."];
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::insert: " . $e->getMessage());
            return ["error" => "Error interno al guardar la factura."];
        }
    }

    public function update($datos)
    {
        try {
            // Recalcular vencimiento si cambia la fecha de emisión
            $fechaEmision = new DateTime($datos['fecha_emision']);
            $fechaVencimiento = clone $fechaEmision;
            $fechaVencimiento->modify('+30 days');

            $sql = "UPDATE facturas SET 
                    idcliente = :idcliente,
                    folio_sii = :folio_sii,
                    fecha_emision = :fecha_emision,
                    fecha_vencimiento = :fecha_vencimiento,
                    monto = :monto
                    WHERE idfactura = :idfactura";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':idcliente' => $datos['idcliente'],
                ':folio_sii' => $datos['folio_sii'],
                ':fecha_emision' => $datos['fecha_emision'],
                ':fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                ':monto' => $datos['monto'],
                ':idfactura' => $datos['idfactura']
            ]);

            return ["success" => "Factura actualizada correctamente."];
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::update: " . $e->getMessage());
            return ["error" => "Error al actualizar la factura."];
        }
    }

    public function softDelete($id)
    {
        try {
            $sql = "UPDATE facturas SET estado = 0 WHERE idfactura = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            return ($stmt->rowCount() > 0) ? ["success" => "Factura eliminada correctamente."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::softDelete: " . $e->getMessage());
            return ["error" => "Error al eliminar la factura."];
        }
    }

    public function cambiarEstado($id, $estado)
    {
        try {
            $sql = "UPDATE facturas SET estado_pago = :estado WHERE idfactura = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':estado' => $estado, ':id' => $id]);
            return ($stmt->rowCount() > 0) ? ["success" => "Estado actualizado."] : ["error" => "No se pudo actualizar."];
        } catch (PDOException $e) {
            error_log("Error en FacturasModel::cambiarEstado: " . $e->getMessage());
            return ["error" => "Error al actualizar el estado."];
        }
    }

    // Método auxiliar para verificar duplicados por folio
    public function existeFolio($folio) {
        $sql = "SELECT COUNT(*) FROM facturas WHERE folio_sii = :folio";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':folio' => $folio]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
<?php
require_once __DIR__ . '/../conexion/conexion.php';

class CotizacionesModel
{
    private $conn = null;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    /**
     * Obtener todas las cotizaciones
     */
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM cotizaciones WHERE estado != 'eliminado' ORDER BY fecha_emision DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cotización por ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM cotizaciones WHERE idcotizacion = :id AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cotizaciones por vehículo
     */
    public function obtenerPorVehiculo($idvehiculo)
    {
        try {
            $sql = "SELECT * FROM cotizaciones WHERE idvehiculo = :idvehiculo AND estado != 'eliminado' ORDER BY fecha_emision DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $idvehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cotizaciones por cliente
     */
    public function obtenerPorCliente($idcliente)
    {
        try {
            $sql = "SELECT c.* FROM cotizaciones c INNER JOIN vehiculos v ON c.idvehiculo = v.idvehiculo WHERE v.idcliente = :idcliente AND c.estado != 'eliminado' ORDER BY c.fecha_emision DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener detalles de cotización con items
     */
    public function obtenerConDetalles($idcotizacion)
    {
        try {
            $sql = "SELECT * FROM cotizaciones_detalles WHERE idcotizacion = :idcotizacion";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcotizacion', $idcotizacion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cotizaciones por vencer (últimos 7 días)
     */
    public function obtenerPorVencer()
    {
        try {
            $sql = "SELECT * FROM cotizaciones WHERE date(fecha_vencimiento) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND estado != 'eliminado' AND estado != 'aceptado' ORDER BY fecha_vencimiento ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cotizaciones vencidas
     */
    public function obtenerVencidas()
    {
        try {
            $sql = "SELECT * FROM cotizaciones WHERE date(fecha_vencimiento) < CURDATE() AND estado != 'eliminado' AND estado != 'aceptado' ORDER BY fecha_vencimiento DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Insertar cotización
     */
    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO cotizaciones (idvehiculo, iddiseniador, fecha_emision, fecha_vencimiento, total_final, estado) VALUES (:idvehiculo, :iddiseniador, :fecha_emision, :fecha_vencimiento, :total_final, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $arr['idvehiculo'], PDO::PARAM_INT);
            $stmt->bindParam(':iddiseniador', $arr['iddiseniador'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_emision', $arr['fecha_emision'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_vencimiento', $arr['fecha_vencimiento'], PDO::PARAM_STR);
            $stmt->bindParam(':total_final', $arr['total_final'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cotización creada correctamente.", "id" => $this->conn->lastInsertId()] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Actualizar cotización
     */
    public function update($arr)
    {
        try {
            $sql = "UPDATE cotizaciones SET idvehiculo = :idvehiculo, iddiseniador = :iddiseniador, fecha_emision = :fecha_emision, fecha_vencimiento = :fecha_vencimiento, total_final = :total_final, estado = :estado WHERE idcotizacion = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idcotizacion'], PDO::PARAM_INT);
            $stmt->bindParam(':idvehiculo', $arr['idvehiculo'], PDO::PARAM_INT);
            $stmt->bindParam(':iddiseniador', $arr['iddiseniador'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_emision', $arr['fecha_emision'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_vencimiento', $arr['fecha_vencimiento'], PDO::PARAM_STR);
            $stmt->bindParam(':total_final', $arr['total_final'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Cotización actualizada."] : ["error" => "No se pudo actualizar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Soft delete
     */
    public function softDelete($id)
    {
        try {
            $sql = "UPDATE cotizaciones SET estado = 'eliminado' WHERE idcotizacion = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cotización eliminada."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
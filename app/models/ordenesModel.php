<?php
require_once __DIR__ . '/../conexion/conexion.php';

class OrdenesModel
{
    private $conn = null;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    /**
     * Obtener todas las órdenes
     */
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE estado != 'eliminado' ORDER BY fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener orden por ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE idorden = :id AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener órdenes por vehículo
     */
    public function obtenerPorVehiculo($idvehiculo)
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE idvehiculo = :idvehiculo AND estado != 'eliminado' ORDER BY fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $idvehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener órdenes por cliente
     */
    public function obtenerPorCliente($idcliente)
    {
        try {
            $sql = "SELECT o.* FROM ordenes_servicio o INNER JOIN vehiculos v ON o.idvehiculo = v.idvehiculo WHERE v.idcliente = :idcliente AND o.estado != 'eliminado' ORDER BY o.fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener órdenes por técnico
     */
    public function obtenerPorTecnico($idtecnico)
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE idtecnico = :idtecnico AND estado != 'eliminado' ORDER BY fecha_creacion DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idtecnico', $idtecnico, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener órdenes pendientes
     */
    public function obtenerPendientes()
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE estado = 'pendiente' ORDER BY fecha_creacion ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Insertar orden de servicio
     */
    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO ordenes_servicio (idvehiculo, idtecnico, fecha_creacion, descripcion, monto, estado) VALUES (:idvehiculo, :idtecnico, :fecha_creacion, :descripcion, :monto, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $arr['idvehiculo'], PDO::PARAM_INT);
            $stmt->bindParam(':idtecnico', $arr['idtecnico'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha_creacion', $arr['fecha_creacion'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':monto', $arr['monto'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Orden creada correctamente.", "id" => $this->conn->lastInsertId()] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Actualizar orden
     */
    public function update($arr)
    {
        try {
            $sql = "UPDATE ordenes_servicio SET idvehiculo = :idvehiculo, idtecnico = :idtecnico, descripcion = :descripcion, monto = :monto, estado = :estado WHERE idorden = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idorden'], PDO::PARAM_INT);
            $stmt->bindParam(':idvehiculo', $arr['idvehiculo'], PDO::PARAM_INT);
            $stmt->bindParam(':idtecnico', $arr['idtecnico'], PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':monto', $arr['monto'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Orden actualizada."] : ["error" => "No se pudo actualizar."];
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
            $sql = "UPDATE ordenes_servicio SET estado = 'eliminado' WHERE idorden = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Orden eliminada."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
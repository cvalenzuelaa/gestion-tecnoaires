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

    public function getAll()
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio ORDER BY fecha_ingreso DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM ordenes_servicio WHERE idorden = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO ordenes_servicio (idorden, idvehiculo, folio, fecha_ingreso, solicitud_cliente, estado) 
                    VALUES (UUID_SHORT(), :idvehiculo, :folio, NOW(), :solicitud, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $arr['idvehiculo']);
            $stmt->bindParam(':folio', $arr['folio']);
            $stmt->bindParam(':solicitud', $arr['solicitud_cliente']);
            $stmt->bindParam(':estado', $arr['estado']);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Orden creada."] : ["error" => "Error al crear."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function softDelete($id)
    {
        try {
            // Hard delete
            $sql = "DELETE FROM ordenes_servicio WHERE idorden = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Orden eliminada."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>

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

    public function getAll()
    {
        try {
            $sql = "SELECT * FROM cotizaciones ORDER BY fecha_emision DESC";
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
            $sql = "SELECT * FROM cotizaciones WHERE idcotizacion = :id";
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
            $sql = "INSERT INTO cotizaciones (idcotizacion, idcliente, idusuario, folio, fecha_emision, validez_dias, total_neto, iva, total_final, estado, ruta_archivo) 
                    VALUES (UUID_SHORT(), :idcliente, :idusuario, :folio, NOW(), :validez, :neto, :iva, :total, :estado, :ruta)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $arr['idcliente']);
            $stmt->bindParam(':idusuario', $arr['idusuario']);
            $stmt->bindParam(':folio', $arr['folio']);
            $stmt->bindParam(':validez', $arr['validez_dias']);
            $stmt->bindParam(':neto', $arr['total_neto']);
            $stmt->bindParam(':iva', $arr['iva']);
            $stmt->bindParam(':total', $arr['total_final']);
            $stmt->bindParam(':estado', $arr['estado']);
            $stmt->bindParam(':ruta', $arr['ruta_archivo']);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cotización creada."] : ["error" => "Error al crear."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function softDelete($id)
    {
        try {
            // Hard delete
            $sql = "DELETE FROM cotizaciones WHERE idcotizacion = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cotización eliminada."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>

<?php
require_once __DIR__ . '/../conexion/conexion.php';

class VehiculosModel
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
            $sql = "SELECT * FROM vehiculos";
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
            $sql = "SELECT * FROM vehiculos WHERE idvehiculo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener historial combinado.
     * Solo Ordenes e Informes (Trabajos realizados).
     */
    public function obtenerHistorial($idvehiculo)
    {
        try {
            $sql = "
            -- Ordenes de Servicio (Vinculadas al Vehículo)
            SELECT 
                'orden' as tipo,
                o.idorden as id,
                o.folio as folio,
                o.fecha_ingreso as fecha,
                NULL as monto,
                o.estado,
                o.solicitud_cliente as descripcion
            FROM ordenes_servicio o
            WHERE o.idvehiculo = :idv1
            
            UNION ALL
            
            -- Informes Técnicos (Vinculados a Ordenes del Vehículo)
            SELECT 
                'informe' as tipo,
                i.idinforme as id,
                i.folio as folio,
                i.fecha_informe as fecha,
                NULL as monto,
                'generado' as estado,
                i.observaciones as descripcion
            FROM informes_tecnicos i
            INNER JOIN ordenes_servicio os ON i.idorden = os.idorden
            WHERE os.idvehiculo = :idv2
            
            ORDER BY fecha DESC
            LIMIT 50
            ";
            
            $stmt = $this->conn->prepare($sql);
            // Usamos parámetros únicos para evitar error HY093 con emulación desactivada
            $stmt->bindParam(':idv1', $idvehiculo, PDO::PARAM_STR);
            $stmt->bindParam(':idv2', $idvehiculo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO vehiculos (idvehiculo, tipo, marca, modelo, patente, descripcion, idcliente) 
                    VALUES (UUID_SHORT(), :tipo, :marca, :modelo, :patente, :descripcion, :idcliente)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tipo', $arr['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $arr['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':modelo', $arr['modelo'], PDO::PARAM_STR);
            $stmt->bindParam(':patente', $arr['patente'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':idcliente', $arr['idcliente'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Vehículo creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function update($arr)
    {
        try {
            $sql = "UPDATE vehiculos SET tipo = :tipo, marca = :marca, modelo = :modelo, patente = :patente, descripcion = :descripcion WHERE idvehiculo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idvehiculo'], PDO::PARAM_STR);
            $stmt->bindParam(':tipo', $arr['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $arr['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':modelo', $arr['modelo'], PDO::PARAM_STR);
            $stmt->bindParam(':patente', $arr['patente'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Vehículo actualizado."] : ["error" => "No se pudo actualizar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function softDelete($id)
    {
        try {
            // Hard delete
            $sql = "DELETE FROM vehiculos WHERE idvehiculo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Vehículo eliminado."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>

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
     * Ordenes e Informes se buscan por idvehiculo.
     * Cotizaciones y Facturas se buscan por idcliente (ya que no tienen idvehiculo en tu DB).
     */
    public function obtenerHistorial($idvehiculo, $idcliente)
    {
        try {
            $sql = "
            -- Cotizaciones (Vinculadas al Cliente)
            SELECT 
                'cotizacion' as tipo,
                c.idcotizacion as id,
                c.folio as folio,
                c.fecha_emision as fecha,
                c.total_final as monto,
                c.estado,
                'Cotización Cliente' as descripcion
            FROM cotizaciones c
            WHERE c.idcliente = :idcliente
            
            UNION ALL
            
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
            WHERE o.idvehiculo = :idvehiculo
            
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
            WHERE os.idvehiculo = :idvehiculo
            
            UNION ALL
            
            -- Facturas (Vinculadas al Cliente)
            SELECT 
                'factura' as tipo,
                f.idfactura as id,
                f.folio_sii as folio,
                f.fecha_vencimiento as fecha,
                f.monto as monto,
                f.estado_pago as estado,
                'Factura Cliente' as descripcion
            FROM facturas f
            WHERE f.idcliente = :idcliente
            
            ORDER BY fecha DESC
            LIMIT 50
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $idvehiculo, PDO::PARAM_STR);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_STR);
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

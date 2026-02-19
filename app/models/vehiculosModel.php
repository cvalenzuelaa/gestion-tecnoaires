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

    /**
     * Obtener todos los vehículos
     */
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM vehiculos WHERE estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener vehículo por ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM vehiculos WHERE idvehiculo = :id AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Buscar vehículo por patente
     */
    public function buscarPorPatente($patente)
    {
        try {
            $sql = "SELECT * FROM vehiculos WHERE patente LIKE :patente AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':patente', "%{$patente}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener vehículos del cliente
     */
    public function obtenerPorCliente($idcliente)
    {
        try {
            $sql = "SELECT * FROM vehiculos WHERE idcliente = :idcliente AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $idcliente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener historial completo de un vehículo
     * (Cotizaciones, Órdenes, Informes, Facturas)
     */
    public function obtenerHistorial($idvehiculo)
    {
        try {
            $sql = "
            SELECT 
                'cotizacion' as tipo,
                c.idcotizacion as id,
                c.fecha_emision as fecha,
                c.total_final as monto,
                c.estado,
                NULL as descripcion
            FROM cotizaciones c
            WHERE c.idvehiculo = :idvehiculo AND c.estado != 'eliminado'
            
            UNION ALL
            
            SELECT 
                'orden' as tipo,
                o.idorden as id,
                o.fecha_creacion as fecha,
                NULL as monto,
                o.estado,
                o.descripcion
            FROM ordenes_servicio o
            WHERE o.idvehiculo = :idvehiculo AND o.estado != 'eliminado'
            
            UNION ALL
            
            SELECT 
                'informe' as tipo,
                i.idinformacion_tecnica as id,
                i.fecha_informe as fecha,
                NULL as monto,
                i.estado,
                i.observaciones as descripcion
            FROM informes_tecnicos i
            WHERE i.idvehiculo = :idvehiculo AND i.estado != 'eliminado'
            
            UNION ALL
            
            SELECT 
                'factura' as tipo,
                f.idfactura as id,
                f.fecha_factura as fecha,
                f.total_factura as monto,
                f.estado,
                NULL as descripcion
            FROM facturas f
            WHERE f.idvehiculo = :idvehiculo AND f.estado != 'eliminado'
            
            ORDER BY fecha DESC
            ";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idvehiculo', $idvehiculo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Insertar vehículo
     */
    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO vehiculos (idcliente, patente, marca, modelo, anio, descripcion, estado) VALUES (:idcliente, :patente, :marca, :modelo, :anio, :descripcion, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idcliente', $arr['idcliente'], PDO::PARAM_INT);
            $stmt->bindParam(':patente', $arr['patente'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $arr['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':modelo', $arr['modelo'], PDO::PARAM_STR);
            $stmt->bindParam(':anio', $arr['anio'], PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Vehículo creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Actualizar vehículo
     */
    public function update($arr)
    {
        try {
            $sql = "UPDATE vehiculos SET patente = :patente, marca = :marca, modelo = :modelo, anio = :anio, descripcion = :descripcion WHERE idvehiculo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idvehiculo'], PDO::PARAM_INT);
            $stmt->bindParam(':patente', $arr['patente'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $arr['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':modelo', $arr['modelo'], PDO::PARAM_STR);
            $stmt->bindParam(':anio', $arr['anio'], PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $arr['descripcion'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Vehículo actualizado."] : ["error" => "No se pudo actualizar."];
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
            $sql = "UPDATE vehiculos SET estado = 'eliminado' WHERE idvehiculo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Vehículo eliminado."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
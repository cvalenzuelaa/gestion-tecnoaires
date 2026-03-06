<?php
require_once __DIR__ . '/../conexion/conexion.php';

class InformesModel
{
    private $conn = null;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function insert($datos)
    {
        try {
            $sql = "INSERT INTO informes_tecnicos (idinforme, idorden, folio, fecha_informe, trabajo_realizado, observaciones, ruta_archivo) 
                    VALUES (UUID_SHORT(), :idorden, :folio, NOW(), :trabajo, :repuestos, :obs, :ruta)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':idorden' => $datos['idorden'],
                ':folio' => $datos['folio'],
                ':trabajo' => $datos['trabajo'],
                ':obs' => $datos['observaciones'],
                ':ruta' => $datos['ruta']
            ]);

            return ($stmt->rowCount() > 0) ? ["success" => "Informe guardado en BD."] : ["error" => "No se pudo guardar el informe en BD."];
        } catch (PDOException $e) {
            return ["error" => "Error en Model: " . $e->getMessage()];
        }
    }
}
?>
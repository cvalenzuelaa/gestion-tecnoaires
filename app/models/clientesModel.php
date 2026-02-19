<?php
require_once __DIR__ . '/../conexion/conexion.php';

class ClientesModel
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
            // Alias rut_empresa as rut for frontend compatibility
            $sql = "SELECT *, rut_empresa as rut FROM clientes ORDER BY fecha_registro DESC";
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
            $sql = "SELECT *, rut_empresa as rut FROM clientes WHERE idcliente = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function buscarPorNombre($nombre)
    {
        try {
            $sql = "SELECT *, rut_empresa as rut FROM clientes WHERE nombre LIKE :nombre OR razon_social LIKE :nombre";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nombre', "%{$nombre}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function buscarPorRut($rut)
    {
        try {
            $sql = "SELECT *, rut_empresa as rut FROM clientes WHERE rut_empresa LIKE :rut";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':rut', "%{$rut}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function insert($arr)
    {
        try {
            // Using UUID_SHORT() for ID
            $sql = "INSERT INTO clientes (idcliente, rut_empresa, nombre, razon_social, direccion, telefono, email, fecha_registro) 
                    VALUES (UUID_SHORT(), :rut, :nombre, :razon_social, :direccion, :telefono, :email, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':rut', $arr['rut'], PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $arr['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':razon_social', $arr['razon_social'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $arr['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $arr['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $arr['email'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cliente creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function update($arr)
    {
        try {
            $sql = "UPDATE clientes SET rut_empresa = :rut, nombre = :nombre, razon_social = :razon_social, direccion = :direccion, telefono = :telefono, email = :email WHERE idcliente = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idcliente'], PDO::PARAM_STR);
            $stmt->bindParam(':rut', $arr['rut'], PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $arr['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':razon_social', $arr['razon_social'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $arr['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $arr['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $arr['email'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Cliente actualizado."] : ["error" => "No se pudo actualizar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function softDelete($id)
    {
        try {
            // Hard delete as there is no 'estado' column
            $sql = "DELETE FROM clientes WHERE idcliente = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cliente eliminado permanentemente."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>

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

    /**
     * Obtener todos los clientes
     */
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM clientes WHERE estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Obtener cliente por ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM clientes WHERE idcliente = :id AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Buscar cliente por nombre, email, rut
     */
    public function buscarPorNombre($nombre)
    {
        try {
            $sql = "SELECT * FROM clientes WHERE (nombre LIKE :nombre OR apellido LIKE :nombre OR razon_social LIKE :nombre) AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nombre', "%{$nombre}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Buscar cliente por RUT
     */
    public function buscarPorRut($rut)
    {
        try {
            $sql = "SELECT * FROM clientes WHERE rut LIKE :rut AND estado != 'eliminado'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':rut', "%{$rut}%", PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Insertar cliente
     */
    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO clientes (idusuario, nombre, apellido, email, telefono, razon_social, rut, direccion, ciudad, estado) VALUES (:idusuario, :nombre, :apellido, :email, :telefono, :razon_social, :rut, :direccion, :ciudad, :estado)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idusuario', $arr['idusuario'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $arr['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $arr['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $arr['email'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $arr['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':razon_social', $arr['razon_social'], PDO::PARAM_STR);
            $stmt->bindParam(':rut', $arr['rut'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $arr['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':ciudad', $arr['ciudad'], PDO::PARAM_STR);
            $stmt->bindParam(':estado', $arr['estado'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cliente creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Actualizar cliente
     */
    public function update($arr)
    {
        try {
            $sql = "UPDATE clientes SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, razon_social = :razon_social, rut = :rut, direccion = :direccion, ciudad = :ciudad WHERE idcliente = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $arr['idcliente'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $arr['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $arr['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $arr['email'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $arr['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':razon_social', $arr['razon_social'], PDO::PARAM_STR);
            $stmt->bindParam(':rut', $arr['rut'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $arr['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':ciudad', $arr['ciudad'], PDO::PARAM_STR);
            $stmt->execute();
            return ($stmt->rowCount() >= 0) ? ["success" => "Cliente actualizado."] : ["error" => "No se pudo actualizar."];
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
            $sql = "UPDATE clientes SET estado = 'eliminado' WHERE idcliente = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0) ? ["success" => "Cliente eliminado."] : ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>
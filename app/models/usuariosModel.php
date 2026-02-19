<?php
require_once __DIR__ . '/../conexion/conexion.php';

class UsuariosModel
{
private $conn = null;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function login($arr)
    {
        try {
            $sql = "SELECT * FROM usuarios WHERE usuario = ? AND pass = ? AND estado = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(empty($result)){
                return [['error' => 'Credenciales incorrectas o cuenta inactiva.']];
            }
            return $result;
        } catch (PDOException $e){
            return [['error' => $e->getMessage()]];
        }
    }

    
    public function getAll() {
        try {
            $sql = "SELECT * FROM usuarios ORDER BY estado DESC, nombre ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT idusuario, usuario, nombre, apellido, rol, estado, fecha_creacion 
                    FROM usuarios WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return null;
        }
    }

    public function insert($arr) {
        try {
            $sql = "INSERT INTO usuarios (idusuario, usuario, nombre, apellido, pass, rol, estado) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['usuario'],
                $arr['nombre'],
                $arr['apellido'],
                sha1($arr['pass']),
                $arr['rol'] ?? 'usuario'
            ]);
            return ($stmt->rowCount() > 0) ? ["success" => "Usuario creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function update($arr) {
        try {
            if (!empty($arr['pass'])) {
                $sql = "UPDATE usuarios 
                        SET usuario=?, nombre=?, apellido=?, pass=?, rol=? 
                        WHERE idusuario=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $arr['usuario'],
                    $arr['nombre'],
                    $arr['apellido'],
                    sha1($arr['pass']),
                    $arr['rol'],
                    $arr['idusuario']
                ]);
            } else {
                $sql = "UPDATE usuarios 
                        SET usuario=?, nombre=?, apellido=?, rol=? 
                        WHERE idusuario=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $arr['usuario'],
                    $arr['nombre'],
                    $arr['apellido'],
                    $arr['rol'],
                    $arr['idusuario']
                ]);
            }
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function softDelete($id) {
        try {
            $sql = "UPDATE usuarios SET estado = 0 WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en softDelete: " . $e->getMessage());
            return false;
        }
    }

    public function activate($id) {
        try {
            $sql = "UPDATE usuarios SET estado = 1 WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en activate: " . $e->getMessage());
            return false;
        }
    }

    public function updateRole($id, $rol) {
        try {
            $sql = "UPDATE usuarios SET rol = ? WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$rol, $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en updateRole: " . $e->getMessage());
            return false;
        }
    }

    public function changePass($arr) {
        try {
            $sql = "UPDATE usuarios SET pass = ? WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([sha1($arr['pass']), $arr['idusuario']]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en changePass: " . $e->getMessage());
            return false;
        }
    }
}
?>

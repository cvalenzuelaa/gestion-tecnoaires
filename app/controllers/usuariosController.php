<?php
require_once __DIR__ . '/../models/usuariosModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
  echo json_encode(array("error" => "No se ha recibido la acción."));
  exit;
}

$obj = new UsuariosController();

switch ($accion) {
    case 'login':
        $usuario = $_POST['usuario'];
        $pass = sha1($_POST['pass']);
        echo json_encode($obj->login([$usuario, $pass]));
        break;

    case 'insert':
        echo json_encode($obj->insert([$_POST['usuario'], $_POST['nombre'], $_POST['apellido'], $_POST['pass'], $_POST['rol']]));
        break;

    case 'update':
        if (isset($_POST['pass']) && !empty($_POST['pass'])) {
            echo json_encode($obj->update([$_POST['usuario'], $_POST['nombre'], $_POST['apellido'], $_POST['pass'], $_POST['rol'], $_POST['idusuario']]));
        } else {
            echo json_encode($obj->updateWithoutPass([$_POST['usuario'], $_POST['nombre'], $_POST['apellido'], $_POST['rol'], $_POST['idusuario']]));
        }
        break;

    case 'changePass':
        $response = $obj->changePass([sha1($_POST['pass']), $_POST['idusuario']]);
        if ($response) {
            echo json_encode(array("success" => "Contraseña actualizada correctamente."));
        } else {
            echo json_encode(array("error" => "Error al actualizar contraseña."));
        }
        break;

    case 'changeRole':
        $response = $obj->updateRole($_POST['idusuario'], $_POST['rol']);
        if ($response) {
            echo json_encode(array("success" => "Rol actualizado correctamente."));
        } else {
            echo json_encode(array("error" => "Error al actualizar rol."));
        }
        break;

    case 'softDelete':
        $response = $obj->softDelete($_POST['idusuario']);
        if ($response) {
            echo json_encode(array("success" => "Usuario desactivado correctamente."));
        } else {
            echo json_encode(array("error" => "Error al desactivar usuario."));
        }
        break;

    case 'activate':
        $response = $obj->activate($_POST['idusuario']);
        if ($response) {
            echo json_encode(array("success" => "Usuario activado correctamente."));
        } else {
            echo json_encode(array("error" => "Error al activar usuario."));
        }
        break;

    case 'getAll':
        echo json_encode($obj->getAll());
        break;

    case 'getById':
        echo json_encode($obj->getById($_POST['idusuario']));
        break;
}

class UsuariosController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UsuariosModel();
    }

    public function login($arr)
    {
        return $this->userModel->login($arr);
    }

    public function insert($arr)
    {
        return $this->userModel->insert([
            'usuario' => $arr[0],
            'nombre' => $arr[1],
            'apellido' => $arr[2],
            'pass' => $arr[3],
            'rol' => $arr[4]
        ]);
    }

    public function update($arr)
    {
        return $this->userModel->update([
            'usuario' => $arr[0],
            'nombre' => $arr[1],
            'apellido' => $arr[2],
            'pass' => $arr[3],
            'rol' => $arr[4],
            'idusuario' => $arr[5]
        ]);
    }

    public function updateWithoutPass($arr)
    {
        return $this->userModel->update([
            'usuario' => $arr[0],
            'nombre' => $arr[1],
            'apellido' => $arr[2],
            'rol' => $arr[3],
            'idusuario' => $arr[4]
        ]);
    }

    public function changePass($arr)
    {
        return $this->userModel->changePass(['pass' => $arr[0], 'idusuario' => $arr[1]]);
    }

    public function updateRole($id, $rol)
    {
        return $this->userModel->updateRole($id, $rol);
    }

    public function softDelete($id)
    {
        return $this->userModel->softDelete($id);
    }

    public function activate($id)
    {
        return $this->userModel->activate($id);
    }

    public function getAll()
    {
        return $this->userModel->getAll();
    }

    public function getById($id)
    {
        return $this->userModel->getById($id);
    }
}
?>
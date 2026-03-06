<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');

// Obtener datos de sesión para pre-llenar (aunque JS lo hará vía AJAX para asegurar datos frescos)
$session = new Session();
$user = $session->getSession();
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="page-title mb-4">
            <h1><i class="fas fa-user-cog me-3"></i>Mi Perfil</h1>
            <p class="mb-0">Actualiza tus datos personales y contraseña</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <form id="formPerfil">
                            <input type="hidden" id="idusuario" name="idusuario" value="<?php echo $user['idusuario']; ?>">
                            
                            <h5 class="mb-3 text-primary border-bottom pb-2">Información Personal</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario">
                                <small id="errorUsuario" class="text-danger" style="display:none"></small>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre">
                                    <small id="errorNombre" class="text-danger" style="display:none"></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido">
                                    <small id="errorApellido" class="text-danger" style="display:none"></small>
                                </div>
                            </div>

                            <h5 class="mb-3 text-primary border-bottom pb-2">Seguridad</h5>
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle me-2"></i>Deja los campos de contraseña vacíos si no deseas cambiarla.
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="pass" name="pass" autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass', 'iconPass')"><i class="fas fa-eye" id="iconPass"></i></button>
                                    </div>
                                    <small id="errorPassNew" class="text-danger" style="display:none"></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="passConfirm">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passConfirm', 'iconPassConfirm')"><i class="fas fa-eye" id="iconPassConfirm"></i></button>
                                    </div>
                                    <small id="errorPass" class="text-danger" style="display:none"></small>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/perfil.js"></script>
<script src="/assets/js/globales.js"></script>
</body>
</html>
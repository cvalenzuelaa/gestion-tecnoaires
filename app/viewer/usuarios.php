<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">
                <h1><i class="fas fa-users-cog me-3"></i>Gestión de Usuarios</h1>
                <p class="mb-0">Administración de accesos y roles</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalUsuario()">
                <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
            </button>
        </div>

        <!-- Pestañas Activos / Inactivos -->
        <ul class="nav nav-tabs mb-3" id="tabUsuarios" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" onclick="cargarUsuarios(1)">Usuarios Activos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inactivos-tab" data-bs-toggle="tab" data-bs-target="#inactivos" type="button" role="tab" onclick="cargarUsuarios(0)">Usuarios Inactivos</button>
            </li>
        </ul>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Rol</th>
                                <th>Fecha Creación</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUsuarios">
                            <tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalUsuario">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" id="idusuario" name="idusuario">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputUsuario" name="usuario" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inputNombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inputApellido" name="apellido" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" id="inputRol" name="rol" required>
                            <option value="admin">Administrador</option>
                            <option value="secretaria">Secretaria</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="inputPass" name="pass">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inputPass', 'iconInputPass')"><i class="fas fa-eye" id="iconInputPass"></i></button>
                        </div>
                        <div class="form-text" id="helpPass">Obligatorio para nuevos usuarios.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarUsuario()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/usuarios.js"></script>
</body>
</html>
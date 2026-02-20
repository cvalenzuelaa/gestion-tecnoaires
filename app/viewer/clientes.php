<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">
                <h1><i class="fas fa-users me-3"></i>Gestión de Clientes</h1>
                <p class="mb-0">Administra la información de tus clientes y empresas</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalCliente()">
                <i class="fas fa-plus me-2"></i>Nuevo Cliente
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaClientes">
                        <thead class="table-light">
                            <tr>
                                <th>RUT</th>
                                <th>Nombre / Razón Social</th>
                                <th>Contacto</th>
                                <th>Dirección</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyClientes">
                            <!-- Carga dinámica por JS -->
                            <tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalCliente">Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCliente">
                    <input type="hidden" id="idcliente" name="idcliente">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">RUT Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rut" name="rut" oninput="limpiarRut(this)" placeholder="12.345.678-9" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Contacto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Razón Social</label>
                        <input type="text" class="form-control" id="razon_social" name="razon_social">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="+56 9...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCliente()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/clientes.js"></script>
</body>
</html>
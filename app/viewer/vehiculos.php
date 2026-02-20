<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">
                <h1><i class="fas fa-truck me-3"></i>Gestión de Vehículos</h1>
                <p class="mb-0">Flota de vehículos y maquinaria registrada</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalVehiculo()">
                <i class="fas fa-plus me-2"></i>Nuevo Vehículo
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaVehiculos">
                        <thead class="table-light">
                            <tr>
                                <th>Patente / ID</th>
                                <th>Vehículo</th>
                                <th>Cliente Asignado</th>
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyVehiculos">
                            <tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalVehiculo">Nuevo Vehículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formVehiculo">
                    <input type="hidden" id="idvehiculo" name="idvehiculo">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cliente Propietario <span class="text-danger">*</span></label>
                        <select class="form-select" id="idcliente" name="idcliente" required>
                            <option value="">Seleccione un cliente...</option>
                            <!-- Carga dinámica -->
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Patente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="patente" name="patente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="automovil">Automóvil</option>
                                <option value="camioneta">Camioneta</option>
                                <option value="suv">SUV</option>
                                <option value="furgon">Furgón</option>
                                <option value="camion">Camión</option>
                                <option value="bus">Bus</option>
                                <option value="maquinaria_agricola">Maquinaria Agrícola</option>
                                <option value="maquinaria_pesada">Maquinaria Pesada</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción / Color / Año</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarVehiculo()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/vehiculos.js"></script>
</body>
</html>
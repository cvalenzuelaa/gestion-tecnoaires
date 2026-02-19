<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <!-- Page Title -->
        <div class="page-title">
            <h1>
                <i class="fas fa-chart-line me-3"></i>Dashboard Administrativo
            </h1>
            <p>Bienvenido al panel de control de TecnoAires</p>
        </div>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-search me-2"></i>Búsqueda Avanzada
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-3">
                                <label for="search_patente" class="form-label">Patente</label>
                                <input type="text" class="form-control" id="search_patente" placeholder="Ej: ABCD12">
                            </div>
                            <div class="col-lg-3">
                                <label for="search_id_equipo" class="form-label">ID Equipo</label>
                                <input type="text" class="form-control" id="search_id_equipo" placeholder="Ej: VEH001">
                            </div>
                            <div class="col-lg-3">
                                <label for="search_cotizacion" class="form-label">Nº Cotización</label>
                                <input type="text" class="form-control" id="search_cotizacion" placeholder="Ej: COT001">
                            </div>
                            <div class="col-lg-3">
                                <label for="search_factura" class="form-label">Nº Factura</label>
                                <input type="text" class="form-control" id="search_factura" placeholder="Ej: FAC001">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                <button class="btn btn-primary" id="btn_buscar">
                                    <i class="fas fa-search me-2"></i>Buscar
                                </button>
                                <button class="btn btn-secondary" id="btn_limpiar">
                                    <i class="fas fa-redo me-2"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados de Búsqueda -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i>Resultados
                    </div>
                    <div class="card-body">
                        <div id="resultados_container">
                            <p class="text-muted text-center">Realiza una búsqueda para ver los resultados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Detalles Completos del Vehículo -->
<div class="modal fade" id="modalDetallesVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-car me-2"></i>Ficha del Vehículo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyDetalles">
                <!-- Contenido cargado dinámicamente -->
                <div class="text-center"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Nueva Orden de Servicio -->
<div class="modal fade" id="modalNuevaOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Orden de Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaOrden">
                    <input type="hidden" id="orden_id_vehiculo" name="idvehiculo">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehículo</label>
                        <input type="text" class="form-control" id="orden_vehiculo_info" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="orden_solicitud" class="form-label">Solicitud del Cliente / Falla Reportada</label>
                        <textarea class="form-control" id="orden_solicitud" name="solicitud_cliente" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="orden_estado" class="form-label">Estado Inicial</label>
                        <select class="form-select" id="orden_estado" name="estado">
                            <option value="ingresado">Ingresado</option>
                            <option value="en_proceso">En Proceso</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="errorNuevaOrden"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarOrden">Crear Orden</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Ver Orden -->
<div class="modal fade" id="modalVerOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>Detalle de Orden</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th class="bg-light" style="width: 30%;">Folio</th>
                        <td id="view_orden_folio" class="fw-bold"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Fecha</th>
                        <td id="view_orden_fecha"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Estado</th>
                        <td id="view_orden_estado"></td>
                    </tr>
                    <tr>
                        <th class="bg-light">Solicitud</th>
                        <td id="view_orden_solicitud"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap y Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script>
</script>

</body>
</html>
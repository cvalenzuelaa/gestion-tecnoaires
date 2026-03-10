<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>

<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="page-title mb-4">
            <h1><i class="fas fa-file-alt me-3"></i>Órdenes de Servicio</h1>
            <p>Generar nueva orden de servicio en formato Word</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form id="formOrdenServicio">
                    <!-- Selección de Cotización -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Seleccionar Cotización Aprobada <span class="text-danger">*</span></label>
                            <select class="form-select" id="idcotizacion" name="idcotizacion" required>
                                <option value="">Cargando cotizaciones...</option>
                            </select>
                            <div class="form-text">Al seleccionar una cotización, se cargarán automáticamente el cliente, vehículo y servicios.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vehículo (Patente) <span class="text-danger">*</span></label>
                            <select class="form-select" id="idvehiculo" name="idvehiculo" required disabled>
                                <option value="">Seleccione una cotización primero</option>
                            </select>
                            <input type="hidden" id="idcliente" name="idcliente">
                        </div>
                    </div>

                    <!-- Campos Automáticos (Readonly) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Razón social</label>
                            <input type="text" class="form-control bg-light" id="info_empresa" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="text" class="form-control bg-light" value="<?php echo date('d/m/Y'); ?>" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" class="form-control bg-light" id="info_direccion" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Contacto</label>
                            <input type="text" class="form-control bg-light" id="info_contacto" readonly>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Modelo</label>
                            <input type="text" class="form-control bg-light" id="info_modelo" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Patente</label>
                            <input type="text" class="form-control bg-light" id="info_patente" readonly>
                        </div>
                    </div>

                    <!-- Tabla de Servicios -->
                    <h5 class="mb-3">Detalle de Servicios</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 10%;">CANT.</th>
                                    <th style="width: 70%;">DESCRIPCIÓN DE SERVICIOS</th>
                                    <th style="width: 20%;">VALOR</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalles">
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-4" onclick="agregarFila()">
                        <i class="fas fa-plus me-2"></i>Agregar Servicio
                    </button>

                    <!-- Totales -->
                    <div class="row justify-content-end mb-4">
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold text-end">Sub-Total:</td>
                                        <td class="text-end"><span id="lblSubTotal">$0</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-end">IVA (19%):</td>
                                        <td class="text-end"><span id="lblIva">$0</span></td>
                                    </tr>
                                    <tr class="table-active">
                                        <td class="fw-bold text-end fs-5">Total:</td>
                                        <td class="text-end fs-5 fw-bold"><span id="lblTotal">$0</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" placeholder="Añadir observaciones adicionales..."></textarea>
                    </div>

                    <!-- Botón de envío -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-file-word me-2"></i>Generar Orden DOCX
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Éxito -->
<div class="modal fade" id="modalExitoOrden" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Orden Generada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-file-word text-primary fa-4x mb-3"></i>
                <p class="fs-5">La orden de servicio ha sido creada.</p>
                <a href="#" id="btnDescargarDoc" class="btn btn-success mt-2" target="_blank">
                    <i class="fas fa-download me-2"></i>Descargar Documento
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/ordenes.js"></script>

</body>
</html>
<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">
                <h1><i class="fas fa-receipt me-3"></i>Gestión de Facturas</h1>
                <p class="mb-0">Control de pagos y vencimientos de facturas (Plazo legal 30 días)</p>
            </div>
            <button class="btn btn-primary" onclick="abrirModalFactura()">
                <i class="fas fa-plus me-2"></i>Ingresar Factura
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaFacturas">
                        <thead class="table-light">
                            <tr>
                                <th>Folio SII</th>
                                <th>Cliente</th>
                                <th>Emisión</th>
                                <th>Vencimiento</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Archivo</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyFacturas">
                            <tr><td colspan="8" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ingreso Factura -->
<div class="modal fade" id="modalFactura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModalFactura">Registrar Nueva Factura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formFactura" enctype="multipart/form-data">
                    <input type="hidden" id="idfactura" name="idfactura">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="idcliente" name="idcliente" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Folio SII <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="folio_sii" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Monto Total <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="monto" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha Emisión <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="fecha_emision" value="<?php echo date('Y-m-d'); ?>" required>
                        <div class="form-text">El vencimiento se calculará automáticamente a 30 días.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivo (PDF/Imagen)</label>
                        <input type="file" class="form-control" name="archivo" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarFactura()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/facturas.js"></script>
</body>
</html>
<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="page-title mb-0">
                <h1><i class="fas fa-file-invoice-dollar me-3"></i>Generar Cotización</h1>
                <p class="mb-0">Crea cotizaciones basadas en plantilla Excel</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form id="formCotizacion">
                    <!-- Cabecera -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" id="idcliente" name="idcliente" required>
                                <option value="">Cargando clientes...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Validez (Días)</label>
                            <input type="number" class="form-control" name="validez_dias" value="15">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Emisión</label>
                            <input type="date" class="form-control" name="fecha_emision" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                    </div>

                    <!-- Detalle de Items -->
                    <h5 class="mb-3 border-bottom pb-2">Detalle de Servicios / Productos</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="tablaDetalles">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%">Descripción</th>
                                    <th style="width: 15%">Cantidad</th>
                                    <th style="width: 20%">Precio Unitario</th>
                                    <th style="width: 15%">Total</th>
                                    <th style="width: 50px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetalles">
                                <!-- Fila inicial -->
                                <tr class="fila-detalle">
                                    <td><input type="text" class="form-control descripcion" required></td>
                                    <td><input type="number" class="form-control cantidad" value="1" min="1" oninput="calcularTotales()"></td>
                                    <td><input type="number" class="form-control precio" value="0" min="0" oninput="calcularTotales()"></td>
                                    <td><input type="text" class="form-control total-linea" readonly></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(this)"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-secondary mb-4" onclick="agregarFila()">
                        <i class="fas fa-plus me-1"></i>Agregar Línea
                    </button>

                    <!-- Totales -->
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold text-end">Neto:</td>
                                    <td class="text-end"><span id="lblNeto">$0</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-end">IVA (19%):</td>
                                    <td class="text-end"><span id="lblIva">$0</span></td>
                                </tr>
                                <tr class="table-active">
                                    <td class="fw-bold text-end fs-5">Total:</td>
                                    <td class="text-end fs-5 fw-bold"><span id="lblTotal">$0</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Guardar y Generar Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Éxito -->
<div class="modal fade" id="modalExitoCotizacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Cotización Generada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <p class="fs-5">La cotización se ha guardado correctamente.</p>
                <a href="#" id="btnDescargarExcel" class="btn btn-primary mt-2" target="_blank"><i class="fas fa-file-excel me-2"></i>Descargar Excel</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/cotizaciones.js"></script>
</body>
</html>
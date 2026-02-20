<?php
require_once('./app/viewer/plantillasAdmin/headAdmin.php');
require_once('./app/viewer/plantillasAdmin/headerAdmin.php');
?>
<div class="main-content">
    <div class="container-dashboard animate-slideUp">
        <div class="page-title mb-4">
            <h1><i class="fas fa-file-word me-3"></i>Informe Técnico</h1>
            <p class="mb-0">Generar informe de servicio para cliente (Word)</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form id="formInforme">
                            <!-- Selección de Orden -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Orden de Servicio Asociada <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                    <select class="form-select" id="idorden" name="idorden" required onchange="cargarDatosOrden()">
                                        <option value="">Seleccione una orden pendiente...</option>
                                        <!-- Carga dinámica -->
                                    </select>
                                </div>
                                <div id="infoOrden" class="form-text mt-2 text-primary fw-bold"></div>
                            </div>

                            <hr>

                            <!-- Campos del Informe -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trabajo Realizado</label>
                                <textarea class="form-control" name="trabajo_realizado" rows="5" placeholder="Describa detalladamente las labores efectuadas..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Repuestos / Insumos Utilizados</label>
                                <textarea class="form-control" name="repuestos" rows="3" placeholder="Listado de materiales..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Observaciones / Recomendaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3" placeholder="Notas finales para el cliente..."></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-file-word me-2"></i>Generar Informe DOCX
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Éxito -->
<div class="modal fade" id="modalExitoInforme" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Informe Generado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-file-word text-primary fa-4x mb-3"></i>
                <p class="fs-5">El informe técnico ha sido creado.</p>
                <a href="#" id="btnDescargarWord" class="btn btn-success mt-2" target="_blank">
                    <i class="fas fa-download me-2"></i>Descargar Documento
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/informes.js"></script>
</body>
</html>
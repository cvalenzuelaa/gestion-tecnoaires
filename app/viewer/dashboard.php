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
            <p>Bienvenido al panel de control de Gestión TecnoAires</p>
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
                                <input type="text" class="form-control" id="search_id_equipo" placeholder="Ej: 101778600943943681">
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
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap y Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/globales.js"></script>
<script src="/assets/js/dashboard-admin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchPatente = document.getElementById('search_patente');
        if (searchPatente) {
            attachPatenteMask(searchPatente);
        }
    });
</script>

</body>
</html>
class DashboardAdmin {
    constructor() {
        this.initElements();
        this.bindEvents();
    }

    // Inicializar elementos del DOM
    initElements() {
        this.searchPatente = document.getElementById('search_patente');
        this.searchIdEquipo = document.getElementById('search_id_equipo');
        this.searchCotizacion = document.getElementById('search_cotizacion');
        this.searchFactura = document.getElementById('search_factura');
        this.btnBuscar = document.getElementById('btn_buscar');
        this.btnLimpiar = document.getElementById('btn_limpiar');
        this.resultadosContainer = document.getElementById('resultados_container');
    }

    // Vincular eventos a los botones
    bindEvents() {
        this.btnBuscar.addEventListener('click', () => this.buscar());
        this.btnLimpiar.addEventListener('click', () => this.limpiar());

        // Permitir búsqueda con Enter en los inputs
        [this.searchPatente, this.searchIdEquipo, this.searchCotizacion, this.searchFactura].forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.buscar();
            });
        });
    }

    // Validar que al menos un campo tenga contenido
    validarBusqueda() {
        const patente = this.searchPatente.value.trim();
        const idEquipo = this.searchIdEquipo.value.trim();
        const cotizacion = this.searchCotizacion.value.trim();
        const factura = this.searchFactura.value.trim();

        if (!patente && !idEquipo && !cotizacion && !factura) {
            this.mostrarError('Por favor ingresa al menos un criterio de búsqueda');
            return false;
        }

        return true;
    }

    // Función principal de búsqueda
    async buscar() {
        // Validar
        if (!this.validarBusqueda()) return;

        // Mostrar loading
        this.mostrarLoading();

        try {
            const datos = {
                accion: 'buscar',
                patente: this.searchPatente.value.trim(),
                id_equipo: this.searchIdEquipo.value.trim(),
                cotizacion: this.searchCotizacion.value.trim(),
                factura: this.searchFactura.value.trim()
            };

            // Llamada AJAX al controller
            const response = await fetch('/app/controllers/vehiculosController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(datos)
            });

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const resultado = await response.json();

            // Verificar si hay error en la respuesta
            if (resultado.error) {
                this.mostrarError(resultado.mensaje || 'Error en la búsqueda');
                return;
            }

            // Mostrar resultados
            if (resultado.data && resultado.data.length > 0) {
                this.renderizarResultados(resultado.data);
            } else {
                this.mostrarSinResultados();
            }

        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.mostrarError('Error al procesar la búsqueda. Intenta de nuevo.');
        }
    }

    // Renderizar resultados de la búsqueda
    renderizarResultados(datos) {
        let html = '<div class="row g-3">';

        datos.forEach(vehiculo => {
            html += `
                <div class="col-lg-12">
                    <div class="card">
                        <!-- Header del Vehículo -->
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <h5 class="mb-0">
                                        <i class="fas fa-car me-2" style="color: #62B145;"></i>
                                        ${vehiculo.marca} ${vehiculo.modelo}
                                    </h5>
                                </div>
                                <div class="col-auto ms-auto">
                                    <span class="badge badge-info">${vehiculo.patente}</span>
                                    <span class="badge badge-success">${vehiculo.id_equipo || 'N/A'}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Vehículo -->
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-secondary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>Información del Vehículo
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td class="fw-bold">Patente:</td>
                                                <td>${vehiculo.patente}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">ID Equipo:</td>
                                                <td>${vehiculo.id_equipo || 'Sin asignar'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Marca:</td>
                                                <td>${vehiculo.marca}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Modelo:</td>
                                                <td>${vehiculo.modelo}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Año:</td>
                                                <td>${vehiculo.anio || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Color:</td>
                                                <td>${vehiculo.color || 'N/A'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-secondary mb-3">
                                        <i class="fas fa-user-tie me-2"></i>Información del Cliente
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td class="fw-bold">Nombre:</td>
                                                <td>${vehiculo.cliente_nombre || 'Sin cliente'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>${vehiculo.cliente_email || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Teléfono:</td>
                                                <td>${vehiculo.cliente_telefono || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">RUT:</td>
                                                <td>${vehiculo.cliente_rut || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Dirección:</td>
                                                <td>${vehiculo.cliente_direccion || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Ciudad:</td>
                                                <td>${vehiculo.cliente_ciudad || 'N/A'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial de Órdenes -->
                            ${this.renderizarHistorial(vehiculo.historial)}

                            <!-- Cotizaciones y Facturas -->
                            ${this.renderizarDocumentos(vehiculo.cotizaciones, vehiculo.facturas)}
                        </div>

                        <!-- Footer con Acciones -->
                        <div class="card-footer bg-light">
                            <button class="btn btn-sm btn-primary me-2" onclick="verDetalles('${vehiculo.id_vehiculo}')">
                                <i class="fas fa-eye me-1"></i>Ver Detalles Completos
                            </button>
                            <button class="btn btn-sm btn-success me-2" onclick="crearOrden('${vehiculo.id_vehiculo}')">
                                <i class="fas fa-plus me-1"></i>Crear Orden
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="descargarReporte('${vehiculo.id_vehiculo}')">
                                <i class="fas fa-download me-1"></i>Descargar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        this.resultadosContainer.innerHTML = html;
    }

    // Renderizar historial de órdenes
    renderizarHistorial(historial) {
        if (!historial || historial.length === 0) {
            return `
                <div class="mt-3 p-3 bg-light rounded">
                    <p class="text-muted mb-0">
                        <i class="fas fa-history me-2"></i>No hay órdenes registradas
                    </p>
                </div>
            `;
        }

        let html = `
            <div class="mt-3">
                <h6 class="text-secondary mb-3">
                    <i class="fas fa-history me-2"></i>Historial de Órdenes (${historial.length})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Orden #</th>
                                <th>Fecha</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        historial.forEach(orden => {
            const estado = this.obtenerBadgeEstado(orden.estado);
            html += `
                <tr>
                    <td><strong>#${orden.id_orden}</strong></td>
                    <td>${this.formatearFecha(orden.fecha_creacion)}</td>
                    <td>${orden.servicio || 'Mantenimiento'}</td>
                    <td>${estado}</td>
                    <td>${orden.tecnico_nombre || 'Sin asignar'}</td>
                    <td>
                        <button class="btn btn-xs btn-info" onclick="verOrden('${orden.id_orden}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        return html;
    }

    // Renderizar cotizaciones y facturas
    renderizarDocumentos(cotizaciones, facturas) {
        let html = '<div class="mt-3 row">';

        // Cotizaciones
        if (cotizaciones && cotizaciones.length > 0) {
            html += `
                <div class="col-md-6">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Cotizaciones (${cotizaciones.length})
                    </h6>
                    <div class="list-group">
            `;

            cotizaciones.forEach(cot => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">#${cot.id_cotizacion}</h6>
                                <small class="text-muted">${this.formatearFecha(cot.fecha_creacion)}</small>
                            </div>
                            <span class="badge badge-warning">${this.formatearMoneda(cot.monto)}</span>
                        </div>
                    </a>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        // Facturas
        if (facturas && facturas.length > 0) {
            html += `
                <div class="col-md-6">
                    <h6 class="text-secondary mb-3">
                        <i class="fas fa-receipt me-2"></i>Facturas (${facturas.length})
                    </h6>
                    <div class="list-group">
            `;

            facturas.forEach(fac => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">#${fac.id_factura}</h6>
                                <small class="text-muted">${this.formatearFecha(fac.fecha_creacion)}</small>
                            </div>
                            <span class="badge badge-success">${this.formatearMoneda(fac.monto)}</span>
                        </div>
                    </a>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        html += '</div>';
        return html;
    }

    // Obtener badge según estado
    obtenerBadgeEstado(estado) {
        const estados = {
            'pendiente': '<span class="badge-warning">Pendiente</span>',
            'en_proceso': '<span class="badge-info">En Proceso</span>',
            'completado': '<span class="badge-success">Completado</span>',
            'cancelado': '<span class="badge-danger">Cancelado</span>'
        };

        return estados[estado] || `<span class="badge-secondary">${estado}</span>`;
    }

    // Limpiar formulario y resultados
    limpiar() {
        this.searchPatente.value = '';
        this.searchIdEquipo.value = '';
        this.searchCotizacion.value = '';
        this.searchFactura.value = '';
        this.resultadosContainer.innerHTML = '<p class="text-muted text-center">Realiza una búsqueda para ver los resultados</p>';
        this.searchPatente.focus();
    }

    // Mostrar loading
    mostrarLoading() {
        this.resultadosContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Buscando...</span>
                </div>
                <p class="text-muted">Buscando información...</p>
            </div>
        `;
    }

    // Mostrar error
    mostrarError(mensaje) {
        this.resultadosContainer.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div>${mensaje}</div>
            </div>
        `;
    }

    // Mostrar sin resultados
    mostrarSinResultados() {
        this.resultadosContainer.innerHTML = `
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fas fa-search me-2"></i>
                <div>No se encontraron resultados para tu búsqueda. Intenta con otros criterios.</div>
            </div>
        `;
    }

    // Utilidades de formato
    formatearFecha(fecha) {
        if (!fecha) return 'N/A';
        const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return new Date(fecha).toLocaleDateString('es-CL', opciones);
    }

    formatearMoneda(monto) {
        if (!monto) return '$0';
        return new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP'
        }).format(monto);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new DashboardAdmin();
});

// Funciones globales para botones de acciones
function verDetalles(idVehiculo) {
    console.log('Ver detalles del vehículo:', idVehiculo);
    // Aquí irá la lógica para ver detalles completos
}

function crearOrden(idVehiculo) {
    console.log('Crear orden para vehículo:', idVehiculo);
    // Aquí irá la lógica para crear una nueva orden
}

function descargarReporte(idVehiculo) {
    console.log('Descargar reporte del vehículo:', idVehiculo);
    // Aquí irá la lógica para descargar el reporte
}

function verOrden(idOrden) {
    console.log('Ver orden:', idOrden);
    // Aquí irá la lógica para ver la orden
}
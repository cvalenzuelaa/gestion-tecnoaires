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
        
        // Elementos para Nueva Orden
        this.btnGuardarOrden = document.getElementById('btnGuardarOrden');
        this.btnGuardarOrden.addEventListener('click', () => this.guardarNuevaOrden());
    }

    // Vincular eventos a los botones
    bindEvents() {
        this.btnBuscar.addEventListener('click', () => this.buscar());
        this.btnLimpiar.addEventListener('click', () => this.limpiar());

        // Implementar Live Search con Debounce
        const debouncedSearch = this.debounce(() => this.buscar(), 500);

        [this.searchPatente, this.searchIdEquipo, this.searchCotizacion, this.searchFactura].forEach(input => {
            // Usamos 'input' para detectar cambios en tiempo real
            input.addEventListener('input', debouncedSearch);
        });
    }

    // Función Debounce para evitar saturar el servidor
    debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
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

        datos.forEach(item => {
            // El controlador devuelve objetos anidados: item.vehiculo, item.cliente, item.historial
            const v = item.vehiculo || {};
            const c = item.cliente || {};
            const h = item.historial || [];
            
            // Mapeo seguro de propiedades para evitar errores si vienen null
            const nombreCliente = c.nombre ? `${c.nombre} ${c.apellido || ''}` : 'Sin cliente';
            const rutCliente = c.rut || 'N/A';
            
            html += `
                <div class="col-lg-12">
                    <div class="card">
                        <!-- Header del Vehículo -->
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <h5 class="mb-0">
                                        <i class="fas fa-car me-2" style="color: #62B145;"></i>
                                        ${v.marca || 'Desconocido'} ${v.modelo || ''}
                                    </h5>
                                </div>
                                <div class="col-auto ms-auto">
                                    <span class="badge badge-info">${v.patente || 'S/P'}</span>
                                    <span class="badge badge-success">${v.idvehiculo || 'N/A'}</span>
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
                                                <td>${v.patente || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">ID Equipo:</td>
                                                <td>${v.idvehiculo || 'Sin asignar'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Marca:</td>
                                                <td>${v.marca || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Modelo:</td>
                                                <td>${v.modelo || '-'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Año:</td>
                                                <td>${v.anio || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Color:</td>
                                                <td>${v.descripcion || 'N/A'}</td>
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
                                                <td>${nombreCliente}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Email:</td>
                                                <td>${c.email || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Teléfono:</td>
                                                <td>${c.telefono || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">RUT:</td>
                                                <td>${rutCliente}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Dirección:</td>
                                                <td>${c.direccion || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Ciudad:</td>
                                                <td>${c.ciudad || 'N/A'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Historial de Órdenes -->
                            ${this.renderizarHistorial(h)}

                            <!-- Cotizaciones y Facturas -->
                            <!-- Nota: El historial unificado ya trae cotizaciones y facturas, pero si necesitas separarlo: -->
                            ${this.renderizarDocumentos(h.filter(x => x.tipo === 'cotizacion'), h.filter(x => x.tipo === 'factura'))}
                        </div>

                        <!-- Footer con Acciones -->
                        <div class="card-footer bg-light">
                            <button class="btn btn-sm btn-primary me-2" onclick="verDetalles('${v.idvehiculo}')">
                                <i class="fas fa-eye me-1"></i>Ver Detalles Completos
                            </button>
                            <button class="btn btn-sm btn-success me-2" onclick="crearOrden('${v.idvehiculo}', '${v.marca} ${v.modelo}', '${v.patente}')">
                                <i class="fas fa-plus me-1"></i>Crear Orden
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="descargarReporte('${v.idvehiculo}')">
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

        // Filtramos solo las ordenes para esta tabla específica, ya que el historial trae todo mezclado
        historial.filter(h => h.tipo === 'orden').forEach(orden => {
            html += `
                <tr>
                    <td><strong>#${orden.id}</strong></td>
                    <td>${this.formatearFecha(orden.fecha)}</td>
                    <td>${orden.descripcion || 'Servicio General'}</td>
                    <td>${this.obtenerBadgeEstado(orden.estado)}</td>
                    <td>-</td>
                    <td>
                        <button class="btn btn-xs btn-info" onclick="verOrden('${orden.id}')">
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
                                <h6 class="mb-1">#${cot.id}</h6>
                                <small class="text-muted">${this.formatearFecha(cot.fecha)}</small>
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
                                <h6 class="mb-1">#${fac.id}</h6>
                                <small class="text-muted">${this.formatearFecha(fac.fecha)}</small>
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

    // Guardar Nueva Orden
    async guardarNuevaOrden() {
        const form = document.getElementById('formNuevaOrden');
        const errorDiv = document.getElementById('errorNuevaOrden');
        const btn = this.btnGuardarOrden;
        
        const idVehiculo = document.getElementById('orden_id_vehiculo').value;
        const solicitud = document.getElementById('orden_solicitud').value.trim();
        const estado = document.getElementById('orden_estado').value;

        if (!solicitud) {
            errorDiv.textContent = 'La solicitud del cliente es obligatoria.';
            errorDiv.classList.remove('d-none');
            return;
        }

        try {
            btn.disabled = true;
            btn.textContent = 'Guardando...';
            errorDiv.classList.add('d-none');

            const datos = new URLSearchParams({
                accion: 'insert',
                idvehiculo: idVehiculo,
                solicitud_cliente: solicitud,
                estado: estado
            });

            const response = await fetch('/app/controllers/ordenesController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: datos
            });

            const res = await response.json();

            if (res.success) {
                // Cerrar modal y recargar búsqueda para ver la nueva orden
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaOrden'));
                modal.hide();
                form.reset();
                alert('Orden creada exitosamente');
                this.buscar(); // Refrescar resultados
            } else {
                throw new Error(res.error || 'Error al crear la orden');
            }
        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Crear Orden';
        }
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
async function verDetalles(idVehiculo) {
    const modalEl = document.getElementById('modalDetallesVehiculo');
    const modalBody = document.getElementById('modalBodyDetalles');
    const modal = new bootstrap.Modal(modalEl);
    
    modal.show();
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando información...</p></div>';

    try {
        // Reutilizamos la búsqueda por ID para traer todo (vehículo, cliente, historial)
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'buscar', id_equipo: idVehiculo })
        });
        
        const res = await response.json();
        
        if (res.error || !res.length) {
            modalBody.innerHTML = '<div class="alert alert-warning">No se pudo cargar la información del vehículo.</div>';
            return;
        }

        // Como el controller devuelve un array, tomamos el primero (búsqueda exacta por ID)
        const data = res[0];
        
        // Renderizamos usando una instancia temporal del Dashboard para aprovechar sus métodos
        const dashboard = new DashboardAdmin();
        // Hack: Limpiamos el container temporalmente para generar el HTML solo de este item
        const tempContainer = document.createElement('div');
        dashboard.resultadosContainer = tempContainer;
        dashboard.renderizarResultados([data]);
        
        modalBody.innerHTML = tempContainer.innerHTML;

    } catch (e) {
        console.error(e);
        modalBody.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
    }
}

function crearOrden(idVehiculo, descripcionVehiculo, patente) {
    const modal = new bootstrap.Modal(document.getElementById('modalNuevaOrden'));
    
    document.getElementById('orden_id_vehiculo').value = idVehiculo;
    document.getElementById('orden_vehiculo_info').value = `${descripcionVehiculo} (${patente})`;
    document.getElementById('orden_solicitud').value = '';
    document.getElementById('errorNuevaOrden').classList.add('d-none');
    
    modal.show();
}

function descargarReporte(idVehiculo) {
    alert('Funcionalidad de descarga de reporte en construcción para ID: ' + idVehiculo);
}

async function verOrden(idOrden) {
    const modal = new bootstrap.Modal(document.getElementById('modalVerOrden'));
    
    try {
        const response = await fetch('/app/controllers/ordenesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', id: idOrden })
        });
        
        const orden = await response.json();
        
        if (orden && !orden.error) {
            document.getElementById('view_orden_folio').textContent = orden.folio || 'S/F';
            document.getElementById('view_orden_fecha').textContent = orden.fecha_ingreso || '-';
            document.getElementById('view_orden_estado').textContent = orden.estado || '-';
            document.getElementById('view_orden_solicitud').textContent = orden.solicitud_cliente || '-';
            modal.show();
        } else {
            alert('No se pudo cargar la orden.');
        }
    } catch (e) {
        console.error(e);
        alert('Error al conectar con el servidor.');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    cargarCotizaciones();
    
    // Al cambiar cotización, cargar todos los datos asociados
    document.getElementById('idcotizacion').addEventListener('change', (e) => {
        cargarDatosDeCotizacion(e.target.value);
    });

    // Al cambiar vehículo, cargar sus datos para llenar los campos readonly
    document.getElementById('idvehiculo').addEventListener('change', (e) => {
        cargarDatosVehiculo(e.target.value);
    });

    // Delegación de eventos para recalcular al escribir en cantidad o precio
    document.getElementById('tbodyDetalles').addEventListener('input', (e) => {
        if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
            calcularTotales();
        }
    });

    document.getElementById('formOrdenServicio').addEventListener('submit', generarOrden);
});

async function cargarCotizaciones() {
    const select = document.getElementById('idcotizacion');
    try {
        const response = await fetch('/app/controllers/cotizacionesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const data = await response.json();
        select.innerHTML = '<option value="">Seleccione una cotización...</option>';
        
        if (Array.isArray(data)) {
            // Filtramos opcionalmente solo las que no estén rechazadas, o mostramos todas
            data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.idcotizacion;
                option.textContent = `Folio: ${c.folio} - ${c.nombre_cliente || 'Cliente'} - ${formatearMoneda(c.total_final)}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando cotizaciones:', error);
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}

async function cargarVehiculos(idCliente, idVehiculoSeleccionado = null) {
    const selectVehiculo = document.getElementById('idvehiculo');
    selectVehiculo.innerHTML = '<option value="">Cargando...</option>';
    selectVehiculo.disabled = true;

    if (!idCliente) {
        selectVehiculo.innerHTML = '<option value="">Seleccione una cotización primero</option>';
        return;
    }

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getByCliente', idcliente: idCliente })
        });
        const vehiculos = await response.json();
        selectVehiculo.innerHTML = '<option value="">Seleccione un vehículo...</option>';
        
        if (Array.isArray(vehiculos) && vehiculos.length > 0) {
            vehiculos.forEach(v => {
                const option = document.createElement('option');
                option.value = v.idvehiculo;
                option.textContent = `${v.patente} - ${v.marca} ${v.modelo}`;
                if (v.idvehiculo === idVehiculoSeleccionado) {
                    option.selected = true;
                }
                selectVehiculo.appendChild(option);
            });
            selectVehiculo.disabled = false;
        } else {
            selectVehiculo.innerHTML = '<option value="">Este cliente no tiene vehículos registrados</option>';
        }
    } catch (error) {
        console.error('Error cargando vehículos:', error);
        selectVehiculo.innerHTML = '<option value="">Error al cargar vehículos</option>';
    }
}

async function cargarDatosDeCotizacion(idCotizacion) {
    limpiarInfoVehiculo();
    document.getElementById('tbodyDetalles').innerHTML = '';
    
    if (!idCotizacion) {
        agregarFila(); // Si no hay selección, dejar una fila vacía
        return;
    }

    try {
        // Obtener detalles de la cotización
        const response = await fetch('/app/controllers/cotizacionesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', id: idCotizacion })
        });
        const cotizacion = await response.json();
        
        if (cotizacion && !cotizacion.error) {
            // 1. Llenar campos ocultos
            document.getElementById('idcliente').value = cotizacion.idcliente;
            
            // CORRECCIÓN: Cargar los vehículos del cliente para poblar el select
            // Esto permite seleccionar el vehículo manualmente si la cotización no trajo el ID
            await cargarVehiculos(cotizacion.idcliente, cotizacion.idvehiculo);

            // 2. Si la cotización tiene vehículo vinculado, seleccionarlo y cargar datos
            if (cotizacion.idvehiculo) {
                cargarDatosVehiculo(cotizacion.idvehiculo);
            }

            // 3. Llenar la tabla de servicios con los items de la cotización
            if (cotizacion.detalles) {
                let detalles = [];
                try {
                    detalles = typeof cotizacion.detalles === 'string' ? JSON.parse(cotizacion.detalles) : cotizacion.detalles;
                } catch(e) { detalles = []; }

                const tbody = document.getElementById('tbodyDetalles');
                detalles.forEach(item => {
                    const row = document.createElement('tr');
                    row.className = 'fila-detalle';
                    row.innerHTML = `
                        <td><input type="number" class="form-control cantidad" value="${item.cantidad}" min="1"></td>
                        <td><input type="text" class="form-control descripcion" value="${item.descripcion}" required></td>
                        <td><input type="number" class="form-control precio" value="${item.precio}" min="0"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(this)"><i class="fas fa-trash"></i></button></td>
                    `;
                    tbody.appendChild(row);
                });
                calcularTotales();
            }
        }

    } catch (error) {
        console.error('Error cargando datos de cotización:', error);
        agregarFila();
    }
}

async function cargarDatosVehiculo(idVehiculo) {
    if (!idVehiculo) {
        limpiarInfoVehiculo();
        return;
    }

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getWithCliente', id: idVehiculo })
        });
        const data = await response.json();
        
        if (data.vehiculo && data.cliente) {
            const v = data.vehiculo;
            const c = data.cliente;
            
            document.getElementById('info_empresa').value = c.razon_social || c.nombre;
            document.getElementById('info_direccion').value = c.direccion || '';
            document.getElementById('info_contacto').value = c.telefono || c.email || '';
            document.getElementById('info_modelo').value = `${v.marca} ${v.modelo}`;
            document.getElementById('info_patente').value = v.patente;
        }
    } catch (error) {
        console.error('Error cargando datos del vehículo:', error);
    }
}

function limpiarInfoVehiculo() {
    document.getElementById('info_empresa').value = '';
    document.getElementById('info_direccion').value = '';
    document.getElementById('info_contacto').value = '';
    document.getElementById('info_modelo').value = '';
    document.getElementById('info_patente').value = '';
}

function agregarFila() {
    const tbody = document.getElementById('tbodyDetalles');
    const row = document.createElement('tr');
    row.className = 'fila-detalle';
    row.innerHTML = `
        <td><input type="number" class="form-control cantidad" value="1" min="1"></td>
        <td><input type="text" class="form-control descripcion" required></td>
        <td><input type="number" class="form-control precio" value="0" min="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    calcularTotales(); // Calcular al agregar una nueva fila
}

function eliminarFila(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.fila-detalle').length > 1) {
        row.remove();
        calcularTotales();
    } else {
        alert('Debe haber al menos una línea de servicio.');
    }
}

function calcularTotales() {
    let subTotal = 0;
    document.querySelectorAll('.fila-detalle').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
        const precio = parseFloat(row.querySelector('.precio').value) || 0;
        subTotal += cantidad * precio;
    });

    const iva = Math.round(subTotal * 0.19);
    const totalFinal = subTotal + iva;

    document.getElementById('lblSubTotal').textContent = formatearMoneda(subTotal);
    document.getElementById('lblIva').textContent = formatearMoneda(iva);
    document.getElementById('lblTotal').textContent = formatearMoneda(totalFinal);
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}

async function generarOrden(e) {
    e.preventDefault();
    const form = e.target;
    
    const detalles = [];
    document.querySelectorAll('.fila-detalle').forEach(row => {
        const descripcion = row.querySelector('.descripcion').value.trim();
        if (!descripcion) return; // No agregar filas vacías
        detalles.push({
            cantidad: row.querySelector('.cantidad').value,
            descripcion: row.querySelector('.descripcion').value,
            precio: row.querySelector('.precio').value
        });
    });

    if (detalles.length === 0) {
        alert('Debe agregar al menos un servicio con descripción.');
        return;
    }

    const formData = new FormData(form);
    const data = {
        accion: 'generarOrdenPDF',
        idcotizacion: formData.get('idcotizacion'),
        idcliente: formData.get('idcliente'),
        idvehiculo: formData.get('idvehiculo'),
        observaciones: formData.get('observaciones'),
        detalles: JSON.stringify(detalles)
    };

    try {
        const response = await fetch('/app/controllers/ordenesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        const res = await response.json();
        
        if (res.success && res.url_archivo) {
            const modal = new bootstrap.Modal(document.getElementById('modalExitoOrden'));
            document.getElementById('btnDescargarDoc').href = res.url_archivo;
            modal.show();
            form.reset();
            document.getElementById('tbodyDetalles').innerHTML = '';
            agregarFila();
            calcularTotales();
            limpiarInfoVehiculo();
        } else {
            alert(res.error || 'Ocurrió un error al generar la orden.');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al servidor.');
    }
}
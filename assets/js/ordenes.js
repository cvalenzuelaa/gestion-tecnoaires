document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    agregarFila(); // Iniciar con una fila

    // Al cambiar cliente, cargar sus vehículos
    document.getElementById('idcliente').addEventListener('change', (e) => {
        cargarVehiculos(e.target.value);
    });

    // Al cambiar vehículo, cargar sus datos para llenar los campos readonly
    document.getElementById('idvehiculo').addEventListener('change', (e) => {
        cargarDatosVehiculo(e.target.value);
    });
});

async function cargarClientes() {
    try {
        const response = await fetch('/app/controllers/clientesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const clientes = await response.json();
        const select = document.getElementById('idcliente');
        select.innerHTML = '<option value="">Seleccione un cliente...</option>';
        
        if (Array.isArray(clientes)) {
            clientes.forEach(c => {
                const option = document.createElement('option');
                option.value = c.idcliente;
                option.textContent = `${c.rut_empresa || c.rut || ''} - ${c.nombre || c.razon_social}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

async function cargarVehiculos(idCliente) {
    const select = document.getElementById('idvehiculo');
    select.innerHTML = '<option value="">Cargando...</option>';
    limpiarInfoVehiculo();
    
    if (!idCliente) {
        select.innerHTML = '<option value="">Seleccione un cliente primero...</option>';
        return;
    }

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getByCliente', idcliente: idCliente })
        });
        const vehiculos = await response.json();
        select.innerHTML = '<option value="">Seleccione un vehículo...</option>';
        
        if (Array.isArray(vehiculos) && vehiculos.length > 0) {
            vehiculos.forEach(v => {
                const option = document.createElement('option');
                option.value = v.idvehiculo;
                option.textContent = `${v.patente} - ${v.marca} ${v.modelo}`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">Este cliente no tiene vehículos registrados</option>';
        }
    } catch (error) {
        console.error('Error cargando vehículos:', error);
        select.innerHTML = '<option value="">Error al cargar vehículos</option>';
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
        <td><input type="number" class="form-control cantidad" value="1" min="1" oninput="calcularTotales()"></td>
        <td><input type="text" class="form-control descripcion" required></td>
        <td><input type="number" class="form-control precio" value="0" min="0" oninput="calcularTotales()"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(this)"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
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
    let total = 0;
    document.querySelectorAll('.fila-detalle').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
        const precio = parseFloat(row.querySelector('.precio').value) || 0;
        
        // Corrección lógica: Multiplicar cantidad por precio unitario
        total += cantidad * precio;
    });

    document.getElementById('lblTotal').textContent = formatearMoneda(total);
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}

document.getElementById('formOrdenServicio').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const detalles = [];
    document.querySelectorAll('.fila-detalle').forEach(row => {
        detalles.push({
            cantidad: row.querySelector('.cantidad').value,
            descripcion: row.querySelector('.descripcion').value,
            precio: row.querySelector('.precio').value
        });
    });

    const formData = new FormData(e.target);
    const data = {
        accion: 'generarOrdenPDF',
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
            e.target.reset();
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
});
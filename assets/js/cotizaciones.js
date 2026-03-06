document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    agregarFila(); // Iniciar con una fila

    // Al cambiar cliente, cargar sus vehículos
    document.getElementById('idcliente').addEventListener('change', (e) => {
        cargarVehiculos(e.target.value);
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
                option.textContent = `${c.rut_empresa || c.rut || ''} - ${c.razon_social || c.nombre}`;
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

function agregarFila() {
    const tbody = document.getElementById('tbodyDetalles');
    const row = document.createElement('tr');
    row.className = 'fila-detalle';
    row.innerHTML = `
        <td><input type="text" class="form-control descripcion" required></td>
        <td><input type="number" class="form-control cantidad" value="1" min="1" oninput="calcularTotales()"></td>
        <td><input type="number" class="form-control precio" value="0" min="0" oninput="calcularTotales()"></td>
        <td><input type="text" class="form-control total-linea" readonly></td>
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
        alert('Debe haber al menos una línea de detalle.');
    }
}

function calcularTotales() {
    let totalNeto = 0;
    document.querySelectorAll('.fila-detalle').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
        const precio = parseFloat(row.querySelector('.precio').value) || 0;
        const totalLinea = cantidad * precio;
        
        row.querySelector('.total-linea').value = formatearMoneda(totalLinea);
        totalNeto += totalLinea;
    });

    const iva = totalNeto * 0.19;
    const totalFinal = totalNeto + iva;

    document.getElementById('lblNeto').textContent = formatearMoneda(totalNeto);
    document.getElementById('lblIva').textContent = formatearMoneda(iva);
    document.getElementById('lblTotal').textContent = formatearMoneda(totalFinal);
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}

document.getElementById('formCotizacion').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const detalles = [];
    document.querySelectorAll('.fila-detalle').forEach(row => {
        detalles.push({
            descripcion: row.querySelector('.descripcion').value,
            cantidad: row.querySelector('.cantidad').value,
            precio: row.querySelector('.precio').value
        });
    });

    const formData = new FormData(e.target);
    const data = {
        accion: 'insert',
        idcliente: formData.get('idcliente'),
        idvehiculo: formData.get('idvehiculo'), // Enviamos el vehículo seleccionado
        validez_dias: formData.get('validez_dias'),
        detalles: JSON.stringify(detalles)
    };

    try {
        const response = await fetch('/app/controllers/cotizacionesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        const res = await response.json();
        
        if (res.success && res.url_archivo) {
            const modal = new bootstrap.Modal(document.getElementById('modalExitoCotizacion'));
            document.getElementById('btnDescargarExcel').href = res.url_archivo;
            modal.show();
            e.target.reset();
            document.getElementById('tbodyDetalles').innerHTML = '';
            agregarFila();
            calcularTotales();
        } else {
            alert(res.error || 'Ocurrió un error al generar la cotización.');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al servidor.');
    }
});
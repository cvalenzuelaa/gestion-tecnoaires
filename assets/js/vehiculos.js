document.addEventListener('DOMContentLoaded', () => {
    cargarVehiculos();
    cargarSelectClientes();
});

const modalVehiculo = new bootstrap.Modal(document.getElementById('modalVehiculo'));
let vehiculosData = [];
let clientesMap = {}; // Para mostrar nombre del cliente en la tabla

async function cargarSelectClientes() {
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
                clientesMap[c.idcliente] = c.nombre + (c.razon_social ? ` (${c.razon_social})` : '');
                const option = document.createElement('option');
                option.value = c.idcliente;
                option.textContent = `${c.rut || ''} - ${c.nombre}`;
                select.appendChild(option);
            });
        }
        // Recargar tabla para actualizar nombres de clientes si llegaron después
        if (vehiculosData.length > 0) renderizarTabla(vehiculosData);

    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

async function cargarVehiculos() {
    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const data = await response.json();
        vehiculosData = data;
        renderizarTabla(data);
    } catch (error) {
        console.error('Error cargando vehículos:', error);
    }
}

function renderizarTabla(data) {
    const tbody = document.getElementById('tbodyVehiculos');
    if (!data || data.length === 0 || data.error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay vehículos registrados</td></tr>';
        return;
    }

    let html = '';
    data.forEach(v => {
        const nombreCliente = clientesMap[v.idcliente] || 'Cargando...';
        
        html += `
            <tr>
                <td>
                    <div class="fw-bold">${v.patente}</div>
                    <small class="text-muted text-xs">ID: ${v.idvehiculo}</small>
                </td>
                <td>
                    <div class="fw-bold">${v.marca} ${v.modelo}</div>
                    <span class="badge bg-info text-dark">${v.tipo}</span>
                </td>
                <td><i class="fas fa-user me-1 text-secondary"></i> ${nombreCliente}</td>
                <td>${v.descripcion || '-'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editarVehiculo('${v.idvehiculo}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarVehiculo('${v.idvehiculo}')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function abrirModalVehiculo() {
    document.getElementById('formVehiculo').reset();
    document.getElementById('idvehiculo').value = '';
    document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
    modalVehiculo.show();
}

function editarVehiculo(id) {
    const vehiculo = vehiculosData.find(v => v.idvehiculo === id);
    if (!vehiculo) return;

    document.getElementById('idvehiculo').value = vehiculo.idvehiculo;
    document.getElementById('idcliente').value = vehiculo.idcliente;
    document.getElementById('patente').value = vehiculo.patente;
    document.getElementById('tipo').value = vehiculo.tipo;
    document.getElementById('marca').value = vehiculo.marca;
    document.getElementById('modelo').value = vehiculo.modelo;
    document.getElementById('descripcion').value = vehiculo.descripcion;

    document.getElementById('tituloModalVehiculo').textContent = 'Editar Vehículo';
    modalVehiculo.show();
}

async function guardarVehiculo() {
    const form = document.getElementById('formVehiculo');
    const formData = new FormData(form);
    const id = formData.get('idvehiculo');
    const accion = id ? 'update' : 'insert';

    if (!formData.get('idcliente') || !formData.get('patente') || !formData.get('marca')) {
        alert('Complete los campos obligatorios (Cliente, Patente, Marca)');
        return;
    }

    const params = new URLSearchParams(formData);
    params.append('accion', accion);

    const response = await fetch('/app/controllers/vehiculosController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    });
    const res = await response.json();

    if (res.success) {
        modalVehiculo.hide();
        cargarVehiculos();
        alert(res.success);
    } else {
        alert(res.error || 'Error al guardar');
    }
}

async function eliminarVehiculo(id) {
    if (!confirm('¿Estás seguro de eliminar este vehículo?')) return;

    const response = await fetch('/app/controllers/vehiculosController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: 'softDelete', id: id })
    });
    const res = await response.json();

    if (res.success) {
        cargarVehiculos();
    } else {
        alert(res.error || 'Error al eliminar');
    }
}
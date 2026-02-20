document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    
    // Inicializar máscara de teléfono del archivo globales.js
    const inputTel = document.getElementById('telefono');
    if(inputTel) attachTelefonoMask(inputTel);
});

const modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));
let clientesData = [];

async function cargarClientes() {
    try {
        const response = await fetch('/app/controllers/clientesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const data = await response.json();
        clientesData = data;
        renderizarTabla(data);
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

function renderizarTabla(data) {
    const tbody = document.getElementById('tbodyClientes');
    if (!data || data.length === 0 || data.error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay clientes registrados</td></tr>';
        return;
    }

    let html = '';
    data.forEach(c => {
        html += `
            <tr>
                <td class="fw-bold">${c.rut || '-'}</td>
                <td>
                    <div class="fw-bold">${c.nombre}</div>
                    <small class="text-muted">${c.razon_social || ''}</small>
                </td>
                <td>
                    <div><i class="fas fa-envelope me-1 text-secondary"></i> ${c.email}</div>
                    <div><i class="fas fa-phone me-1 text-secondary"></i> ${c.telefono || '-'}</div>
                </td>
                <td>${c.direccion || '-'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editarCliente('${c.idcliente}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente('${c.idcliente}')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function abrirModalCliente() {
    document.getElementById('formCliente').reset();
    document.getElementById('idcliente').value = '';
    document.getElementById('tituloModalCliente').textContent = 'Nuevo Cliente';
    modalCliente.show();
}

function editarCliente(id) {
    const cliente = clientesData.find(c => c.idcliente === id);
    if (!cliente) return;

    document.getElementById('idcliente').value = cliente.idcliente;
    document.getElementById('rut').value = cliente.rut || '';
    document.getElementById('nombre').value = cliente.nombre;
    document.getElementById('razon_social').value = cliente.razon_social || '';
    document.getElementById('email').value = cliente.email;
    document.getElementById('telefono').value = cliente.telefono || '';
    document.getElementById('direccion').value = cliente.direccion || '';

    document.getElementById('tituloModalCliente').textContent = 'Editar Cliente';
    modalCliente.show();
}

async function guardarCliente() {
    const form = document.getElementById('formCliente');
    const formData = new FormData(form);
    const id = formData.get('idcliente');
    const accion = id ? 'update' : 'insert';

    // Validaciones básicas
    if (!formData.get('rut') || !formData.get('nombre') || !formData.get('email')) {
        alert('Por favor complete los campos obligatorios (RUT, Nombre, Email)');
        return;
    }

    const params = new URLSearchParams(formData);
    params.append('accion', accion);

    const response = await fetch('/app/controllers/clientesController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    });
    const res = await response.json();

    if (res.success) {
        modalCliente.hide();
        cargarClientes();
        alert(res.success);
    } else {
        alert(res.error || 'Error al guardar');
    }
}

async function eliminarCliente(id) {
    if (!confirm('¿Estás seguro de eliminar este cliente? Esta acción no se puede deshacer.')) return;

    const response = await fetch('/app/controllers/clientesController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ accion: 'softDelete', id: id })
    });
    const res = await response.json();

    if (res.success) {
        cargarClientes();
    } else {
        alert(res.error || 'Error al eliminar');
    }
}
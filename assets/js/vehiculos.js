document.addEventListener('DOMContentLoaded', () => {
    cargarVehiculos();
    
    // Inicializar máscara y validación de patente
    const inputPatente = document.getElementById('patente');
    if (inputPatente) {
        attachPatenteMask(inputPatente);
    }
});

async function cargarVehiculos() {
    const tbody = document.getElementById('tbodyVehiculos');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const data = await response.json();
        
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay vehículos registrados.</td></tr>';
            return;
        }

        data.forEach(v => {
            const row = `
                <tr>
                    <td class="fw-bold">${v.patente}</td>
                    <td>${v.marca} ${v.modelo}</td>
                    <td><span class="badge bg-secondary">${v.tipo}</span></td>
                    <td>${v.descripcion || '-'}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarVehiculo('${v.idvehiculo}')"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarVehiculo('${v.idvehiculo}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

function abrirModalVehiculo() {
    document.getElementById('formVehiculo').reset();
    document.getElementById('idvehiculo').value = '';
    document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
    
    // Limpiar validaciones visuales
    const inputPatente = document.getElementById('patente');
    const errorPatente = document.getElementById('errorPatente');
    inputPatente.style.border = '';
    errorPatente.style.display = 'none';
    
    cargarClientesSelect();
    new bootstrap.Modal(document.getElementById('modalVehiculo')).show();
}

async function cargarClientesSelect(seleccionado = null) {
    const select = document.getElementById('idcliente');
    if (select.options.length > 1 && !seleccionado) return;

    try {
        const response = await fetch('/app/controllers/clientesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const clientes = await response.json();
        select.innerHTML = '<option value="">Seleccione un cliente...</option>';
        clientes.forEach(c => {
            const selected = seleccionado == c.idcliente ? 'selected' : '';
            select.innerHTML += `<option value="${c.idcliente}" ${selected}>${c.rut_empresa || c.rut} - ${c.nombre}</option>`;
        });
    } catch (e) { console.error(e); }
}

async function guardarVehiculo() {
    const form = document.getElementById('formVehiculo');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Validación estricta de Patente antes de enviar
    const inputPatente = document.getElementById('patente');
    const errorPatente = document.getElementById('errorPatente');
    if (!validaPatente(inputPatente, errorPatente)) {
        return; // Detiene el guardado si la patente no es válida
    }

    const formData = new FormData(form);
    const id = formData.get('idvehiculo');
    formData.append('accion', id ? 'update' : 'insert');

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalVehiculo')).hide();
            cargarVehiculos();
            alert(res.success);
        } else {
            alert(res.error || 'Error al guardar');
        }
    } catch (e) { console.error(e); }
}

async function editarVehiculo(id) {
    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', id: id })
        });
        const v = await response.json();
        
        if (v && !v.error) {
            document.getElementById('idvehiculo').value = v.idvehiculo;
            document.getElementById('patente').value = v.patente;
            document.getElementById('tipo').value = v.tipo;
            document.getElementById('marca').value = v.marca;
            document.getElementById('modelo').value = v.modelo;
            document.getElementById('descripcion').value = v.descripcion;
            
            document.getElementById('tituloModalVehiculo').textContent = 'Editar Vehículo';
            
            // Limpiar validaciones visuales al abrir editar
            const inputPatente = document.getElementById('patente');
            const errorPatente = document.getElementById('errorPatente');
            inputPatente.style.border = '';
            errorPatente.style.display = 'none';

            await cargarClientesSelect(v.idcliente);
            
            new bootstrap.Modal(document.getElementById('modalVehiculo')).show();
        } else {
            alert(v.error || 'Error al cargar vehículo');
        }
    } catch (e) { console.error(e); }
}

async function eliminarVehiculo(id) {
    if (!confirm('¿Estás seguro de eliminar este vehículo?')) return;

    try {
        const response = await fetch('/app/controllers/vehiculosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'eliminar', id: id })
        });
        const res = await response.json();
        if (res.success) {
            cargarVehiculos();
        } else {
            alert(res.error);
        }
    } catch (e) { console.error(e); }
}
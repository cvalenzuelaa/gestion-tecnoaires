document.addEventListener('DOMContentLoaded', () => {
    cargarFacturas();
});

async function cargarFacturas() {
    const tbody = document.getElementById('tbodyFacturas');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const data = await response.json();
        
        tbody.innerHTML = '';

        // VALIDACIÓN: Si hay error o no es array, mostramos mensaje y detenemos
        if (data.error) {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">${data.error}</td></tr>`;
            return;
        }
        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay facturas registradas.</td></tr>';
            return;
        }

        data.forEach(f => {
            const esPagada = f.estado_pago === 'pagada';
            const badge = esPagada 
                ? '<span class="badge bg-success">Pagada</span>' 
                : '<span class="badge bg-warning text-dark">Pendiente</span>';
            
            const btnPago = esPagada
                ? `<button class="btn btn-sm btn-warning" onclick="confirmarDeshacerPago('${f.idfactura}')" title="Deshacer pago"><i class="fas fa-undo"></i></button>`
                : `<button class="btn btn-sm btn-success" onclick="confirmarPago('${f.idfactura}', '${f.folio_sii}')" title="Marcar como pagada"><i class="fas fa-check"></i></button>`;

            const btnEditar = `<button class="btn btn-sm btn-info text-white" onclick="editarFactura('${f.idfactura}')" title="Editar"><i class="fas fa-edit"></i></button>`;
            const btnEliminar = `<button class="btn btn-sm btn-danger" onclick="eliminarFactura('${f.idfactura}')" title="Eliminar"><i class="fas fa-trash"></i></button>`;

            const linkArchivo = f.ruta_archivo_pdf 
                ? `<a href="${f.ruta_archivo_pdf}" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file-pdf"></i></a>`
                : '<span class="text-muted">-</span>';

            const row = `
                <tr>
                    <td class="fw-bold">${f.folio_sii}</td>
                    <td>${f.nombre_cliente}</td>
                    <td>${formatearFecha(f.fecha_emision)}</td>
                    <td class="${validarVencimiento(f.fecha_vencimiento, f.estado_pago)}">${formatearFecha(f.fecha_vencimiento)}</td>
                    <td>${formatearMoneda(f.monto)}</td>
                    <td>${badge}</td>
                    <td>${linkArchivo}</td>
                    <td class="text-end">
                        <div class="btn-group">
                            ${btnPago}
                            ${btnEditar}
                            ${btnEliminar}
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

function validarVencimiento(fechaVenc, estado) {
    if (estado === 'pagada') return 'text-success';
    const hoy = new Date();
    const venc = new Date(fechaVenc);
    const diffTime = venc - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return 'text-danger fw-bold'; // Vencida
    if (diffDays <= 7) return 'text-warning fw-bold'; // Por vencer
    return '';
}

function abrirModalFactura() {
    cargarClientesSelect();
    document.getElementById('formFactura').reset();
    document.getElementById('idfactura').value = '';
    document.getElementById('tituloModalFactura').textContent = 'Registrar Nueva Factura';
    new bootstrap.Modal(document.getElementById('modalFactura')).show();
}

async function cargarClientesSelect() {
    const select = document.getElementById('idcliente');
    if (select.options.length > 1) return; // Ya cargado

    try {
        const response = await fetch('/app/controllers/clientesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const clientes = await response.json();
        select.innerHTML = '<option value="">Seleccione un cliente...</option>';
        clientes.forEach(c => {
            select.innerHTML += `<option value="${c.idcliente}">${c.rut_empresa} - ${c.nombre}</option>`;
        });
    } catch (e) { console.error(e); }
}

async function guardarFactura() {
    const form = document.getElementById('formFactura');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('accion', 'guardar');

    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalFactura')).hide();
            cargarFacturas();
            alert('Factura guardada correctamente');
        } else {
            alert(res.error || 'Error al guardar');
        }
    } catch (e) { console.error(e); }
}

function confirmarPago(id, folio) {
    if (confirm(`¿Confirmas que la factura folio ${folio} ha sido pagada?`)) {
        cambiarEstado(id, 'pagada');
    }
}

function confirmarDeshacerPago(id) {
    if (confirm('¿Estás seguro de que deseas deshacer el pago de esta factura? Volverá a estado Pendiente.')) {
        cambiarEstado(id, 'pendiente');
    }
}

async function cambiarEstado(id, estado) {
    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'cambiarEstado', id: id, estado: estado })
        });
        const res = await response.json();
        if (res.success) {
            cargarFacturas();
        } else {
            alert(res.error);
        }
    } catch (e) { console.error(e); }
}

async function editarFactura(id) {
    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', id: id })
        });
        const data = await response.json();
        
        if (data && !data.error) {
            const form = document.getElementById('formFactura');
            form.idfactura.value = data.idfactura;
            
            // Asegurar que los clientes estén cargados antes de asignar valor
            await cargarClientesSelect();
            form.idcliente.value = data.idcliente;
            
            form.folio_sii.value = data.folio_sii;
            form.monto.value = data.monto;
            form.fecha_emision.value = data.fecha_emision;

            document.getElementById('tituloModalFactura').textContent = 'Editar Factura';
            new bootstrap.Modal(document.getElementById('modalFactura')).show();
        } else {
            alert('No se pudo cargar la información de la factura.');
        }
    } catch (error) {
        console.error(error);
    }
}

async function eliminarFactura(id) {
    if (!confirm('¿Estás seguro de eliminar esta factura?')) return;

    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'eliminar', id: id })
        });
        const res = await response.json();
        if (res.success) cargarFacturas();
        else alert(res.error || 'Error al eliminar');
    } catch (error) {
        console.error(error);
    }
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-CL');
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}
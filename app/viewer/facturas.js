document.addEventListener('DOMContentLoaded', () => {
    cargarFacturas();
    cargarClientes();
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
                option.textContent = `${c.rut_empresa || c.rut} - ${c.nombre}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error cargando clientes:', error);
    }
}

async function cargarFacturas() {
    const tbody = document.getElementById('tbodyFacturas');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const facturas = await response.json();
        
        tbody.innerHTML = '';
        
        if (Array.isArray(facturas) && facturas.length > 0) {
            facturas.forEach(f => {
                const estadoBadge = f.estado_pago === 'pagado' 
                    ? '<span class="badge bg-success">Pagado</span>' 
                    : '<span class="badge bg-warning text-dark">Pendiente</span>';
                
                // Botón de acción de pago (Pagar o Deshacer)
                let btnPago = '';
                if (f.estado_pago === 'pendiente') {
                    btnPago = `<button class="btn btn-sm btn-success" onclick="cambiarEstado('${f.idfactura}', 'pagado')" title="Marcar como Pagado"><i class="fas fa-check"></i></button>`;
                } else {
                    // Botón Deshacer con confirmación
                    btnPago = `<button class="btn btn-sm btn-warning" onclick="confirmarDeshacerPago('${f.idfactura}')" title="Deshacer Pago"><i class="fas fa-undo"></i></button>`;
                }

                const row = `
                    <tr>
                        <td class="fw-bold">${f.folio_sii}</td>
                        <td>${f.nombre_cliente}</td>
                        <td>${f.descripcion || '<span class="text-muted fst-italic">Sin descripción</span>'}</td>
                        <td>${formatearFecha(f.fecha_emision)}</td>
                        <td>${formatearFecha(f.fecha_vencimiento)}</td>
                        <td>${formatearMoneda(f.monto)}</td>
                        <td>${estadoBadge}</td>
                        <td>
                            ${f.ruta_archivo_pdf ? `<a href="${f.ruta_archivo_pdf}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf"></i></a>` : '-'}
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                ${btnPago}
                                <button class="btn btn-sm btn-info text-white" onclick="editarFactura('${f.idfactura}')" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarFactura('${f.idfactura}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No hay facturas registradas.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

function abrirModalFactura() {
    document.getElementById('formFactura').reset();
    document.getElementById('idfactura').value = '';
    document.getElementById('tituloModalFactura').textContent = 'Registrar Nueva Factura';
    new bootstrap.Modal(document.getElementById('modalFactura')).show();
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
            form.idcliente.value = data.idcliente;
            form.folio_sii.value = data.folio_sii;
            form.monto.value = data.monto;
            form.fecha_emision.value = data.fecha_emision;
            if(form.descripcion) form.descripcion.value = data.descripcion || '';

            document.getElementById('tituloModalFactura').textContent = 'Editar Factura';
            new bootstrap.Modal(document.getElementById('modalFactura')).show();
        } else {
            alert('No se pudo cargar la información de la factura.');
        }
    } catch (error) {
        console.error(error);
    }
}

async function guardarFactura() {
    const form = document.getElementById('formFactura');
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
            alert(res.success);
        } else {
            alert(res.error || 'Error al guardar.');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión.');
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
        if (res.success) cargarFacturas();
        else alert(res.error);
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
        else alert(res.error);
    } catch (error) {
        console.error(error);
    }
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    const [year, month, day] = fecha.split('-');
    return `${day}/${month}/${year}`;
}
document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    calcularTotales();
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
                option.textContent = `${c.rut || ''} - ${c.nombre}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
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
    let neto = 0;
    document.querySelectorAll('.fila-detalle').forEach(row => {
        const cant = parseFloat(row.querySelector('.cantidad').value) || 0;
        const precio = parseFloat(row.querySelector('.precio').value) || 0;
        const totalLinea = cant * precio;
        
        row.querySelector('.total-linea').value = formatearMoneda(totalLinea);
        neto += totalLinea;
    });

    const iva = neto * 0.19;
    const total = neto + iva;

    document.getElementById('lblNeto').textContent = formatearMoneda(neto);
    document.getElementById('lblIva').textContent = formatearMoneda(iva);
    document.getElementById('lblTotal').textContent = formatearMoneda(total);
}

function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(valor);
}

document.getElementById('formCotizacion').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Recopilar datos
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
        validez_dias: formData.get('validez_dias'),
        detalles: JSON.stringify(detalles) // Enviamos detalles como JSON string
    };

    try {
        // Nota: Aquí deberías apuntar a un controller que maneje la generación del Excel
        // Por ahora simulamos el envío al cotizacionesController
        const response = await fetch('/app/controllers/cotizacionesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        
        const res = await response.json();
        
        if (res.success) {
            // Mostrar modal y link de descarga (simulado)
            const modal = new bootstrap.Modal(document.getElementById('modalExitoCotizacion'));
            // Si el backend devuelve la URL del archivo generado:
            if (res.url_archivo) {
                document.getElementById('btnDescargarExcel').href = res.url_archivo;
            }
            modal.show();
            e.target.reset();
            // Resetear tabla
            document.getElementById('tbodyDetalles').innerHTML = '';
            agregarFila();
            calcularTotales();
        } else {
            alert(res.error || 'Error al guardar');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión');
    }
});
document.addEventListener('DOMContentLoaded', () => {
    cargarOrdenesPendientes();
});

let ordenesMap = {};

async function cargarOrdenesPendientes() {
    try {
        const response = await fetch('/app/controllers/ordenesController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll' })
        });
        const ordenes = await response.json();
        const select = document.getElementById('idorden');
        
        if (Array.isArray(ordenes)) {
            ordenes.forEach(o => {
                ordenesMap[o.idorden] = o;
                const option = document.createElement('option');
                option.value = o.idorden;
                option.textContent = `OS: ${o.folio || o.idorden} - ${o.fecha_ingreso} - ${o.nombre_cliente || o.cliente || 'Cliente no registrado'}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function cargarDatosOrden() {
    const id = document.getElementById('idorden').value;
    const infoDiv = document.getElementById('infoOrden');
    
    if (id && ordenesMap[id]) {
        const o = ordenesMap[id];
        infoDiv.textContent = `Solicitud Original: ${o.solicitud_cliente}`;
    } else {
        infoDiv.textContent = '';
    }
}

document.getElementById('formInforme').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('accion', 'insert');

    try {
        // Aquí apuntarías a informesController.php
        // Este controller debe usar PHPWord para abrir template.docx y reemplazar variables
        const response = await fetch('/app/controllers/informesController.php', {
            method: 'POST',
            body: formData // Enviamos FormData directamente para soportar archivos
        });
        
        const res = await response.json();
        
        if (res.success) {
            const modal = new bootstrap.Modal(document.getElementById('modalExitoInforme'));
            if (res.url_archivo) {
                document.getElementById('btnDescargarWord').href = res.url_archivo;
            }
            modal.show();
            e.target.reset();
            document.getElementById('infoOrden').textContent = '';
        } else {
            alert(res.error || 'Error al generar informe');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión');
    }
});
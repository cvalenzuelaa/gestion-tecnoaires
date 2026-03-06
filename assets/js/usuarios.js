document.addEventListener('DOMContentLoaded', () => {
    cargarUsuarios(1); // Cargar activos por defecto
});

let estadoActual = 1;

async function cargarUsuarios(estado) {
    estadoActual = estado;
    const tbody = document.getElementById('tbodyUsuarios');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getAll', estado: estado })
        });
        const data = await response.json();
        
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay usuarios en esta lista.</td></tr>';
            return;
        }

        data.forEach(u => {
            let btnAccion = '';
            if (estado === 1) {
                btnAccion = `<button class="btn btn-sm btn-outline-danger" onclick="cambiarEstado('${u.idusuario}', 'softDelete')" title="Desactivar"><i class="fas fa-user-slash"></i></button>`;
            } else {
                btnAccion = `<button class="btn btn-sm btn-outline-success" onclick="cambiarEstado('${u.idusuario}', 'activate')" title="Activar"><i class="fas fa-user-check"></i></button>`;
            }

            const row = `
                <tr>
                    <td class="fw-bold">${u.usuario}</td>
                    <td>${u.nombre} ${u.apellido}</td>
                    <td><span class="badge bg-info text-dark">${u.rol}</span></td>
                    <td>${new Date(u.fecha_creacion).toLocaleDateString()}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editarUsuario('${u.idusuario}')"><i class="fas fa-edit"></i></button>
                        ${btnAccion}
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

function abrirModalUsuario() {
    document.getElementById('formUsuario').reset();
    document.getElementById('idusuario').value = '';
    document.getElementById('tituloModalUsuario').textContent = 'Nuevo Usuario';
    document.getElementById('helpPass').textContent = 'Obligatorio para nuevos usuarios.';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

async function guardarUsuario() {
    const form = document.getElementById('formUsuario');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const id = formData.get('idusuario');
    formData.append('accion', id ? 'update' : 'insert');

    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            cargarUsuarios(estadoActual);
            alert(res.success);
        } else {
            alert(res.error || 'Error al guardar');
        }
    } catch (e) { console.error(e); }
}

async function editarUsuario(id) {
    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', idusuario: id })
        });
        const u = await response.json();
        
        if (u && !u.error) {
            document.getElementById('idusuario').value = u.idusuario;
            document.getElementById('inputUsuario').value = u.usuario;
            document.getElementById('inputNombre').value = u.nombre;
            document.getElementById('inputApellido').value = u.apellido;
            document.getElementById('inputRol').value = u.rol;
            document.getElementById('inputPass').value = '';
            document.getElementById('helpPass').textContent = 'Dejar en blanco para mantener la actual.';
            
            document.getElementById('tituloModalUsuario').textContent = 'Editar Usuario';
            new bootstrap.Modal(document.getElementById('modalUsuario')).show();
        }
    } catch (e) { console.error(e); }
}

async function cambiarEstado(id, accion) {
    if (!confirm('¿Estás seguro de realizar esta acción?')) return;

    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: accion, idusuario: id })
        });
        const res = await response.json();
        if (res.success) {
            cargarUsuarios(estadoActual);
        } else {
            alert(res.error);
        }
    } catch (e) { console.error(e); }
}
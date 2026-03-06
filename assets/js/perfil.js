document.addEventListener('DOMContentLoaded', () => {
    cargarDatosPerfil();
    configurarValidaciones();
});

async function cargarDatosPerfil() {
    const idUsuario = document.getElementById('idusuario').value;
    if (!idUsuario) return;

    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'getById', idusuario: idUsuario })
        });
        const user = await response.json();
        
        if (user && !user.error) {
            document.getElementById('usuario').value = user.usuario;
            document.getElementById('nombre').value = user.nombre;
            document.getElementById('apellido').value = user.apellido;
        }
    } catch (error) {
        console.error('Error cargando perfil:', error);
    }
}

function configurarValidaciones() {
    const usuario = document.getElementById('usuario');
    const nombre = document.getElementById('nombre');
    const apellido = document.getElementById('apellido');
    const pass = document.getElementById('pass');
    const passConfirm = document.getElementById('passConfirm');

    const errorUsuario = document.getElementById('errorUsuario');
    const errorNombre = document.getElementById('errorNombre');
    const errorApellido = document.getElementById('errorApellido');
    const errorPassNew = document.getElementById('errorPassNew');
    const errorPassConfirm = document.getElementById('errorPass');

    // Eventos Input para validación en tiempo real
    usuario.addEventListener('input', () => {
        if (usuario.value.trim().length < 4) {
            errorUsuario.textContent = 'Mínimo 4 caracteres.';
            formatoInputError(usuario, errorUsuario);
        } else {
            formatoInputExito(usuario, errorUsuario);
        }
    });

    nombre.addEventListener('input', () => validaNombres(nombre, errorNombre));
    apellido.addEventListener('input', () => validaNombres(apellido, errorApellido));
    
    pass.addEventListener('input', () => {
        if (pass.value.trim() !== '') {
            validaPass(pass, errorPassNew);
            if (passConfirm.value.trim() !== '') validaPassConfirm(pass, passConfirm, errorPassConfirm);
        } else {
            // Limpiar errores si borra la contraseña
            errorPassNew.style.display = 'none';
            pass.style.border = '';
            errorPassConfirm.style.display = 'none';
            passConfirm.style.border = '';
            passConfirm.value = '';
        }
    });

    passConfirm.addEventListener('input', () => {
        if (pass.value.trim() !== '') validaPassConfirm(pass, passConfirm, errorPassConfirm);
    });
}

document.getElementById('formPerfil').addEventListener('submit', async (e) => {
    e.preventDefault();

    const usuario = document.getElementById('usuario');
    const nombre = document.getElementById('nombre');
    const apellido = document.getElementById('apellido');
    const pass = document.getElementById('pass');
    const passConfirm = document.getElementById('passConfirm');

    const errorUsuario = document.getElementById('errorUsuario');
    const errorNombre = document.getElementById('errorNombre');
    const errorApellido = document.getElementById('errorApellido');
    const errorPassNew = document.getElementById('errorPassNew');
    const errorPassConfirm = document.getElementById('errorPass');

    let valid = true;

    // Validar todos los campos antes de enviar
    if (!validaNombres(nombre, errorNombre)) valid = false;
    if (!validaNombres(apellido, errorApellido)) valid = false;
    
    if (usuario.value.trim().length < 4) {
        errorUsuario.textContent = 'Mínimo 4 caracteres.';
        formatoInputError(usuario, errorUsuario);
        valid = false;
    }

    // Validar contraseña solo si el campo tiene texto
    if (pass.value.trim() !== '') {
        if (!validaPass(pass, errorPassNew)) valid = false;
        if (!validaPassConfirm(pass, passConfirm, errorPassConfirm)) valid = false;
    }
    
    if (!valid) return;

    const formData = new FormData(e.target);
    formData.append('accion', 'updateProfile');

    try {
        const response = await fetch('/app/controllers/usuariosController.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            alert(res.success);
            if (res.logout) {
                window.location.href = '/'; // Redirigir al login si cambió contraseña
            } else {
                // Si solo actualizó datos, recargar para ver cambios en header
                location.reload();
            }
        } else {
            alert(res.error || 'Error al actualizar perfil.');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión.');
    }
});
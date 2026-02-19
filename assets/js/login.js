document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-login');
    const inputUser = document.getElementById('nombreusuario');
    const inputPass = document.getElementById('pass');
    const userError = document.getElementById('correo-error');
    const passError = document.getElementById('pass-error');
    const btnLogin = document.getElementById('btn-login');

    function showError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
    }
    function hideError(el) {
        if (!el) return;
        el.style.display = 'none';
    }

    // Función para verificar y actualizar estado del botón
    const updateButtonState = () => {
        const usuario = (inputUser.value || '').trim();
        const pass = (inputPass.value || '').trim();
        
        if (usuario.length > 0 && pass.length > 0) {
            btnLogin.disabled = false;
            btnLogin.style.opacity = '1';
            btnLogin.style.cursor = 'pointer';
        } else {
            btnLogin.disabled = true;
            btnLogin.style.opacity = '0.5';
            btnLogin.style.cursor = 'not-allowed';
        }
    };

    // Desactivar botón al cargar la página
    updateButtonState();

    // Actualizar estado del botón cuando el usuario cambia
    inputUser.addEventListener('input', updateButtonState);
    inputPass.addEventListener('input', updateButtonState);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError(userError);
        hideError(passError);

        const usuario = (inputUser.value || '').trim();
        const pass = (inputPass.value || '').trim();

        // Validaciones previas - mostrar errores solo en submit
        if (!usuario) {
            showError(userError, 'El nombre de usuario es obligatorio');
            formatoInputError(inputUser, userError);
            return;
        }

        if (usuario.length < 5) {
            showError(userError, 'El nombre de usuario debe tener al menos 5 caracteres');
            formatoInputError(inputUser, userError);
            return;
        }

        if (!pass) {
            showError(passError, 'La contraseña es obligatoria');
            formatoInputError(inputPass, passError);
            return;
        }

        if (!validaPass(inputPass, passError)) {
            formatoInputError(inputPass, passError);
            return;
        }

        try {
            // Desactivar botón durante el envío
            btnLogin.disabled = true;
            btnLogin.textContent = 'Validando...';

            // 1) Validar credenciales contra usuariosController.php
            const validateRes = await fetch('/app/controllers/usuariosController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    accion: 'login',
                    usuario: usuario, 
                    pass: pass 
                })
            });
            const validateJson = await validateRes.json();

            // El modelo devuelve un array
            if (!validateJson || !Array.isArray(validateJson) || validateJson.length === 0) {
                showError(passError, 'Respuesta inválida del servidor');
                formatoInputError(inputPass, passError);
                btnLogin.disabled = false;
                btnLogin.textContent = 'INICIAR SESIÓN';
                return;
            }

            const firstResult = validateJson[0];
            if (firstResult.error) {
                showError(passError, firstResult.error);
                formatoInputError(inputPass, passError);
                btnLogin.disabled = false;
                btnLogin.textContent = 'INICIAR SESIÓN';
                return;
            }

            // Si llegamos aquí, las credenciales son válidas
            const user = firstResult;
            if (!user || !user.idusuario) {
                showError(passError, 'Datos de usuario inválidos');
                formatoInputError(inputPass, passError);
                btnLogin.disabled = false;
                btnLogin.textContent = 'INICIAR SESIÓN';
                return;
            }

            // 2) Iniciar sesión en servidor (sessionStart.php)
            const sessionRes = await fetch('/app/sesiones/sessionStart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    idusuario: user.idusuario,
                    usuario: user.usuario,
                    nombre: user.nombre || '',
                    apellido: user.apellido || '',
                    rol: user.rol || ''
                })
            });
            const sessionJson = await sessionRes.json();

            if (!sessionJson || !sessionJson.success) {
                const msg = (sessionJson && sessionJson.message) ? sessionJson.message : 'No se pudo iniciar sesión';
                showError(passError, msg);
                formatoInputError(inputPass, passError);
                btnLogin.disabled = false;
                btnLogin.textContent = 'INICIAR SESIÓN';
                return;
            }

            // 3) Redirección al root - index.php maneja el enrutamiento según el rol
            window.location.href = '/';
        } catch (err) {
            showError(passError, 'Error de conexión. Intente más tarde.');
            formatoInputError(inputPass, passError);
            btnLogin.disabled = false;
            btnLogin.textContent = 'INICIAR SESIÓN';
            console.error(err);
        }
    });
});
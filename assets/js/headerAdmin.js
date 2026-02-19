document.addEventListener('DOMContentLoaded', () => {
    const btnLogout = document.getElementById('btn-logout');

    if (btnLogout) {
        btnLogout.addEventListener('click', async (e) => {
            e.preventDefault();

            // Confirmación
            if (!confirm('¿Deseas cerrar sesión?')) {
                return;
            }

            try {
                // Llamar a sessionClose.php
                const response = await fetch('/app/sesiones/sessionClose.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (data.success) {
                    // Redirigir al login
                    window.location.href = '/';
                } else {
                    alert('Error al cerrar sesión: ' + (data.message || 'Intenta de nuevo'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión. Intenta de nuevo.');
            }
        });
    }
});
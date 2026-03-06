document.addEventListener('DOMContentLoaded', () => {
    verificarNotificaciones();
    // Verificar cada 60 segundos
    setInterval(verificarNotificaciones, 60000);

    // Lógica de Cierre de Sesión
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) return;

            try {
                await fetch('/app/controllers/usuariosController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ accion: 'logout' })
                });
                window.location.href = '/';
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
                window.location.href = '/';
            }
        });
    }
});

async function verificarNotificaciones() {
    const badge = document.getElementById('badgeNotif');
    const lista = document.getElementById('listaNotif');

    try {
        const response = await fetch('/app/controllers/facturasController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ accion: 'checkNotificaciones' })
        });
        const data = await response.json();

        if (data.cantidad > 0) {
            badge.textContent = data.cantidad;
            badge.classList.remove('d-none');
            
            let html = `<li><h6 class="dropdown-header text-warning">Facturas por Vencer (${data.cantidad})</h6></li>`;
            
            data.detalles.forEach(f => {
                const dias = parseInt(f.dias_restantes);
                let textoDias = '';
                let color = '';

                if (dias < 0) {
                    textoDias = `Venció hace ${Math.abs(dias)} días`;
                    color = 'text-danger';
                } else if (dias === 0) {
                    textoDias = 'Vence HOY';
                    color = 'text-danger fw-bold';
                } else {
                    textoDias = `Vence en ${dias} días`;
                    color = 'text-warning';
                }

                html += `
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/?nav=facturas">
                        <div class="fw-bold">${f.nombre_cliente}</div>
                        <small>Folio: ${f.folio_sii}</small><br>
                        <small class="${color}"><i class="fas fa-clock me-1"></i>${textoDias}</small>
                    </a></li>
                `;
            });
            lista.innerHTML = html;
        } else {
            badge.classList.add('d-none');
            lista.innerHTML = '<li><span class="dropdown-item-text text-white-50 text-center">No hay notificaciones pendientes</span></li>';
        }
    } catch (error) {
        console.error('Error verificando notificaciones:', error);
    }
}
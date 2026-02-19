<?php
require_once('./app/sesiones/session.php');
$session = new Session();
$user = $session->getSession();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-admin">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="/?nav=dashboard_admin">
            <img src="/assets/img/tecnoaires_logo.png" alt="TecnoAires" class="logo-navbar">
            <span class="brand-text">TecnoAires</span>
            <span class="badge">Admin</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="/?nav=dashboard_admin">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?nav=usuarios">
                        <i class="fas fa-users-cog"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?nav=clientes">
                        <i class="fas fa-user-tie"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/?nav=vehiculos">
                        <i class="fas fa-car"></i>
                        <span>Vehículos</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-tasks"></i>
                        <span>Gestión</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="/?nav=ordenes"><i class="fas fa-list-check"></i> Órdenes</a></li>
                        <li><a class="dropdown-item" href="/?nav=cotizaciones"><i class="fas fa-file-invoice-dollar"></i> Cotizaciones</a></li>
                        <li><a class="dropdown-item" href="/?nav=facturas"><i class="fas fa-receipt"></i> Facturas</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/?nav=informes"><i class="fas fa-chart-bar"></i> Informes</a></li>
                    </ul>
                </li>
            </ul>

            <div class="user-menu ms-3">
                <div class="dropdown">
                    <button class="btn btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user['nombre'] ?? 'Usuario'); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars($user['usuario'] ?? ''); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/?nav=perfil"><i class="fas fa-cog"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" id="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
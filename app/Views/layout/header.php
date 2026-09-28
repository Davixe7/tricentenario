<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <aside class="sidebar">
        <!-- Logo Sidebar Top Header -->
        <div class="p-3 text-center border-bottom d-flex align-items-center justify-content-center gap-2">
            <img src="<?= BASE_URL ?>/assets/img/phillips-66.png" alt="Phillips 66" style="max-height: 38px;">
            <img src="<?= BASE_URL ?>/assets/img/happy-gas.png" alt="Happy Gas" style="max-height: 38px;">
        </div>

        <nav class="nav flex-column mt-3">
            <!-- 1. Configuración (Solo Administrador General) -->
            <?php if ((Auth::user()['role'] ?? '') === 'General Admin'): ?>
                <div class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#configDropdown" role="button" aria-expanded="false" aria-controls="configDropdown">
                        <span class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/assets/img/config-icon.png" alt="Configuración" style="width: 22px; height: 22px;" class="me-2">
                            Configuración
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse ms-3" id="configDropdown">
                        <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/areas">
                            <i class="bi bi-grid-fill me-2"></i>Áreas
                        </a>
                        <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/positions">
                            <i class="bi bi-briefcase-fill me-2"></i>Puestos
                        </a>
                        <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/users">
                            <i class="bi bi-people-fill me-2"></i>Usuarios
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2. Capacitación (Visible para todos) -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#capacitacionDropdown" role="button" aria-expanded="false" aria-controls="capacitacionDropdown">
                    <span class="d-flex align-items-center">
                        <img src="<?= BASE_URL ?>/assets/img/capacitacion-icon.png" alt="Capacitación" style="width: 22px; height: 22px;" class="me-2">
                        Capacitación
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="capacitacionDropdown">
                    <a class="nav-link py-2 fs-7 text-muted" href="#">
                        <i class="bi bi-circle me-2"></i>Módulos
                    </a>
                </div>
            </div>

            <!-- 3. Plantillas (Visible para todos) -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#plantillasDropdown" role="button" aria-expanded="false" aria-controls="plantillasDropdown">
                    <span class="d-flex align-items-center">
                        <img src="<?= BASE_URL ?>/assets/img/plantillas-icon.png" alt="Plantillas" style="width: 22px; height: 22px;" class="me-2">
                        Plantillas
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="plantillasDropdown">
                    <a class="nav-link py-2 fs-7 text-muted" href="#">
                        <i class="bi bi-circle me-2"></i>Documentos
                    </a>
                </div>
            </div>

            <!-- 4. Boleta (Visible para todos) -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#boletaDropdown" role="button" aria-expanded="false" aria-controls="boletaDropdown">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-check-fill me-2 fs-5 text-secondary"></i>
                        Boleta
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="boletaDropdown">
                    <a class="nav-link py-2 fs-7 text-muted" href="#">
                        <i class="bi bi-circle me-2"></i>Consultar
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="flex-grow-1">
        <header class="bg-brand-dark text-white p-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Modelo de Desarrollo Humano Integral</span>
            <div>
                <span class="me-3"><?= htmlspecialchars(Auth::user()['name'] ?? '') ?></span>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </header>
        <main class="p-4">
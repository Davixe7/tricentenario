<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        /* Ajuste para evitar desbordamiento horizontal en layouts Flexbox */
        .main-wrapper {
            min-width: 0;
            overflow-x: hidden;
        }
        .sidebar {
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed {
            margin-left: -260px; /* Ajusta este valor al ancho real de tu sidebar */
        }
    </style>
</head>
<body>
<div class="d-flex w-100 overflow-hidden">
    <aside class="sidebar">
        <div class="p-3 text-center border-bottom d-flex align-items-center justify-content-center gap-2">
            <img src="<?= BASE_URL ?>/assets/img/phillips-66.png" alt="Phillips 66" style="max-height: 38px;">
            <img src="<?= BASE_URL ?>/assets/img/happy-gas.png" alt="Happy Gas" style="max-height: 38px;">
        </div>

        <nav class="nav flex-column mt-3">
            <?php if (in_array(Auth::user()['role'] ?? '', ['General Admin', 'admin', 'Administrador General'])): ?>
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

            <!-- MÓDULO CAPACITACIÓN -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#capacitacionDropdown" role="button" aria-expanded="false" aria-controls="capacitacionDropdown">
                    <span class="d-flex align-items-center">
                        <img src="<?= BASE_URL ?>/assets/img/capacitacion-icon.png" alt="Capacitación" style="width: 22px; height: 22px;" class="me-2">
                        Capacitación
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="capacitacionDropdown">
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/trainings">
                        <i class="bi bi-list-task me-2"></i>Carga Masiva
                    </a>
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/trainings/create">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Carga
                    </a>
                </div>
            </div>

            <!-- MÓDULO PLANTILLAS -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#plantillasDropdown" role="button" aria-expanded="false" aria-controls="plantillasDropdown">
                    <span class="d-flex align-items-center">
                        <img src="<?= BASE_URL ?>/assets/img/plantillas-icon.png" alt="Plantillas" style="width: 22px; height: 22px;" class="me-2">
                        Plantillas
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="plantillasDropdown">
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/templates/reporte-semanal">
                        <i class="bi bi-file-earmark-excel me-2"></i>1. Reporte Semanal
                    </a>
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/templates/bitacora-incidencias">
                        <i class="bi bi-file-earmark-excel me-2"></i>2. Bitácora de Incidencias
                    </a>
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/templates/calificaciones-360">
                        <i class="bi bi-file-earmark-excel me-2"></i>3. Calificaciones 360º
                    </a>
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/templates/proyecto-semestre">
                        <i class="bi bi-file-earmark-excel me-2"></i>4. Proyecto Semestre
                    </a>
                </div>
            </div>

            <!-- MÓDULO BOLETA -->
            <div class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between" data-bs-toggle="collapse" href="#boletaDropdown" role="button" aria-expanded="false" aria-controls="boletaDropdown">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-check-fill me-2 fs-5 text-secondary"></i>
                        Boleta
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse ms-3" id="boletaDropdown">
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/scorecard/general">
                        <i class="bi bi-circle me-2"></i>General
                    </a>
                    <a class="nav-link py-2 fs-7" href="<?= BASE_URL ?>/scorecard/individuals">
                        <i class="bi bi-circle me-2"></i>Individuales
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="flex-grow-1 main-wrapper d-flex flex-column" style="min-width: 0;">
        <header class="bg-brand-dark text-white px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-link text-white p-0 text-decoration-none" onclick="document.querySelector('.sidebar').classList.toggle('collapsed');" title="Alternar Menú">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <span class="fw-bold fs-5">Modelo de Desarrollo Humano Integral</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-link text-white p-0 position-relative text-decoration-none me-2" title="Notificaciones">
                    <i class="bi bi-bell fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger p-1" style="font-size: 0.65rem;">
                        1
                    </span>
                </button>

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php 
                            $userSession = Auth::user() ?? $_SESSION['user'] ?? [];
                            $userId = $userSession['id'] ?? null;
                            $photo = $userSession['avatar'] ?? '';

                            if (empty($photo) && $userId && class_exists('Database')) {
                                try {
                                    $db = Database::getInstance();
                                    $stmt = $db->prepare("SELECT avatar, first_name, last_name_paternal FROM users WHERE id = ?");
                                    $stmt->execute([$userId]);
                                    $uData = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($uData && !empty($uData['avatar'])) {
                                        $photo = $uData['avatar'];
                                        $_SESSION['user']['avatar'] = $photo;
                                    }
                                } catch (\Throwable $e) {}
                            }

                            $firstName = $userSession['first_name'] ?? '';
                            $lastName = $userSession['last_name_paternal'] ?? '';
                            $userName = trim("$firstName $lastName");
                            if (empty($userName)) {
                                $userName = $userSession['name'] ?? $userSession['username'] ?? 'Usuario';
                            }

                            $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=0D8ABC&color=fff';

                            if (!empty($photo)) {
                                $cleanPath = ltrim($photo, '/');
                                if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
                                    $avatarSrc = $cleanPath;
                                } elseif (str_starts_with($cleanPath, 'uploads/')) {
                                    $avatarSrc = BASE_URL . '/' . $cleanPath;
                                } else {
                                    $avatarSrc = BASE_URL . '/uploads/' . $cleanPath;
                                }
                            } else {
                                $avatarSrc = $defaultAvatar;
                            }
                        ?>
                        <img src="<?= htmlspecialchars($avatarSrc) ?>" 
                             alt="Avatar" 
                             class="rounded-circle me-2 object-fit-cover" 
                             style="width: 36px; height: 36px; border: 1px solid rgba(255,255,255,0.3);"
                             onerror="this.onerror=null; this.src='<?= $defaultAvatar ?>';">
                        <span class="fw-semibold me-1"><?= htmlspecialchars($userName) ?></span>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                        <li>
                            <button type="button" class="dropdown-item py-2 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="bi bi-key me-2 text-primary"></i> Cambiar contraseña
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center text-danger" href="<?= BASE_URL ?>/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="modal fade text-dark" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-brand-dark text-white">
                        <h5 class="modal-title" id="changePasswordModalLabel"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="<?= BASE_URL ?>/change-password" method="POST">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Contraseña Actual</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="••••••••">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Repite la contraseña">
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <main class="p-4 flex-grow-1" style="min-width: 0;">
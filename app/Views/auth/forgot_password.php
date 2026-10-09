<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" 
      style="background: url('<?= BASE_URL ?>/assets/img/login-bg.jpg') no-repeat center center fixed; background-size: cover;">

<div class="card login-card p-4 shadow-lg border-0" style="max-width: 500px; width: 100%; background-color: rgba(255, 255, 255, 0.96); border-radius: 12px;">
    <h4 class="fw-bold text-center text-brand-dark mb-1">Recuperar Contraseña</h4>
    <p class="text-center text-muted mb-4 fs-6">Ingresa tu usuario o correo electrónico registrado</p>

    <?php if (!empty($_SESSION['flash_info'])): ?>
        <div class="alert alert-info p-3 fs-6" style="word-break: break-all;">
            <?= $_SESSION['flash_info']; unset($_SESSION['flash_info']); ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/forgot-password" method="POST">
        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
        
        <div class="input-group mb-4">
            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
            <input type="text" name="identifier" class="form-control" placeholder="Usuario o correo electrónico" required>
        </div>

        <button type="submit" class="btn btn-brand-primary w-100 fw-semibold py-2 mb-3" style="background-color: var(--brand-dark);">Generar Enlace</button>

        <div class="text-center">
            <a href="<?= BASE_URL ?>/login" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Volver al Inicio de Sesión</a>
        </div>
    </form>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" 
      style="background: url('<?= BASE_URL ?>/assets/img/login-bg.jpg') no-repeat center center fixed; background-size: cover;">

<div class="card login-card p-4 shadow-lg border-0" style="max-width: 500px; width: 100%; background-color: rgba(255, 255, 255, 0.96); border-radius: 12px;">
    <h4 class="fw-bold text-center text-brand-dark mb-1">Nueva Contraseña</h4>
    <p class="text-center text-muted mb-4 fs-6">Establece tu nueva contraseña de acceso</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger p-2 fs-6 mb-3"><?= htmlspecialchars($error) ?></div>
        <div class="text-center">
            <a href="<?= BASE_URL ?>/forgot-password" class="btn btn-outline-secondary btn-sm">Solicitar un nuevo enlace</a>
        </div>
    <?php else: ?>
        <form action="<?= BASE_URL ?>/reset-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Nueva Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmar Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repite la contraseña" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-brand-primary w-100 fw-semibold py-2 mb-3" style="background-color: var(--brand-dark);">Guardar Contraseña</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
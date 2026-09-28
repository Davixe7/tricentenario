<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

<div class="card login-card p-4" style="max-width: 750px; width: 100%;">
    <div class="row align-items-center">
        <div class="col-md-7 border-end pe-4">
            <h4 class="fw-bold text-center text-brand-dark mb-1">Modelo de Desarrollo Humano Integral</h4>
            <p class="text-center text-muted mb-4 fs-6">Sistema de Gestión</p>
            
            <p class="fw-semibold">Hola, Bienvenido.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger p-2 fs-6"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Usuario o correo" required>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                </div>

                <button type="submit" class="btn btn-brand-primary w-100 fw-semibold py-2" style="background-color: var(--brand-dark);">Entrar</button>

                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none text-muted small">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
        <div class="col-md-5 text-center ps-4">
            <div class="py-3">
                <i class="bi bi-fuel-pump-fill display-1 text-warning"></i>
                <div class="fw-bold mt-2 fs-5">HAPPY GAS</div>
            </div>
            <hr>
            <div class="py-2">
                <div class="fw-bold text-danger fs-4">PHILLIPS 66</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
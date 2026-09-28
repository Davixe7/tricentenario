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
        <div class="p-3 text-center border-bottom">
            <h5 class="fw-bold text-brand-dark mb-0">Gestión</h5>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link" href="<?= BASE_URL ?>/areas"><i class="bi bi-grid-fill me-2"></i>Áreas</a>
            <a class="nav-link" href="<?= BASE_URL ?>/positions"><i class="bi bi-briefcase-fill me-2"></i>Puestos</a>
            <a class="nav-link" href="<?= BASE_URL ?>/users"><i class="bi bi-people-fill me-2"></i>Usuarios</a>
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
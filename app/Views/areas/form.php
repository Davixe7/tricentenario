<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold text-brand-dark mb-3">Configuración / Áreas</h4>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <h5 class="fw-bold mb-4"><?= $area ? 'Editar Área' : 'Nueva Área' ?></h5>

        <form action="<?= $area ? BASE_URL . '/areas/update/' . $area['id'] : BASE_URL . '/areas/store' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre del Área: <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($area['name'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Estatus: <span class="text-danger">*</span></label>
                <select name="status" class="form-select">
                    <option value="active" <?= (($area['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactive" <?= (($area['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/areas" class="btn btn-light border">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
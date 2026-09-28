<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold text-brand-dark mb-3">Configuración / Puestos</h4>

<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <h5 class="fw-bold mb-4"><?= $position ? 'Editar Puesto' : 'Nuevo Puesto' ?></h5>

        <form action="<?= $position ? BASE_URL . '/positions/update/' . $position['id'] : BASE_URL . '/positions/store' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre del Puesto: <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($position['name'] ?? '') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Estatus: <span class="text-danger">*</span></label>
                <select name="status" class="form-select">
                    <option value="active" <?= (($position['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactive" <?= (($position['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/positions" class="btn btn-light border">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
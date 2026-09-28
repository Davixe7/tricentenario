<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold text-brand-dark mb-3">Configuración / Usuarios</h4>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <ul class="nav nav-tabs border-0">
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="#">Lista de Usuarios</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <form method="GET" action="<?= BASE_URL ?>/users" class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-bold">Estatus:</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Activo</option>
                        <option value="inactive" <?= ($statusFilter === 'inactive') ? 'selected' : '' ?>>Inactivo</option>
                        <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>
                <a href="<?= BASE_URL ?>/users/create" class="btn btn-brand-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Nuevo Usuario</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Área</th>
                        <th>Rol</th>
                        <th>Usuario</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay registros disponibles.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name_paternal'] . ' ' . ($u['last_name_maternal'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($u['position_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['area_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['role']) ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/users/edit/<?= $u['id'] ?>" class="action-icon" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <form action="<?= BASE_URL ?>/users/delete/<?= $u['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Desea desactivar este usuario?');">
                                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                        <button type="submit" class="border-0 bg-transparent p-0 action-icon delete" data-bs-toggle="tooltip" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
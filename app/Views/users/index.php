<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-brand-dark mb-0">Configuración / Usuarios</h4>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
    </a>
</div>

<!-- Filtros por Estatus y Búsqueda -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/users" class="row g-2 align-items-center">
            <div class="col-md-3">
                <?php $statusVal = $_GET['status'] ?? 'active'; ?>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="active" <?= $statusVal === 'active' ? 'selected' : '' ?>>Activos</option>
                    <option value="inactive" <?= $statusVal === 'inactive' ? 'selected' : '' ?>>Inactivos</option>
                    <option value="all" <?= $statusVal === 'all' ? 'selected' : '' ?>>Todos los estatus</option>
                </select>
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, correo, usuario..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                    <?php if (!empty($_GET['q']) || (isset($_GET['status']) && $_GET['status'] !== 'active')): ?>
                        <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">Limpiar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Usuarios -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estatus</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['employee_number'] ?? '-') ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name_paternal'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                                <td>
                                    <span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $u['status'] === 'active' ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/users/delete/<?= $u['id'] ?>" class="d-inline" onsubmit="return confirm('¿Desea eliminar este usuario?')">
                                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron usuarios.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
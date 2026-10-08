<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-easel me-2 text-primary"></i>Gestión de Capacitaciones</h4>
            <p class="text-muted small mb-0">Listado de cargas masivas y cursos registrados en el sistema</p>
        </div>
        <a href="<?= BASE_URL ?>/trainings/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nueva Carga Masiva
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Capaciación creada y colaboradores asignados correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> La capacitación ha sido eliminada.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nombre / Nombre de Carga</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Duración</th>
                            <th class="text-center">Colaboradores</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trainings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No hay capacitaciones registradas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trainings as $item): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            <?= htmlspecialchars($item['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                                        <?= date('d/m/Y', strtotime($item['training_date'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['duration_hours'] ?: 'N/A') ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-primary px-3">
                                            <?= $item['total_users'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/trainings/show?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Ver Asignados">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <form action="<?= BASE_URL ?>/trainings/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta capacitación? Se eliminará del historial de los colaboradores.');">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
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
</div>
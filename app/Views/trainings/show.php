<div class="container-fluid">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/trainings" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Volver a Capacitaciones
        </a>
        <h4 class="fw-bold mt-2"><i class="bi bi-journal-check me-2 text-primary"></i>Detalle de la Capacitación</h4>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-brand-dark text-white fw-semibold py-3">
                    Información
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><?= htmlspecialchars($training['title']) ?></h5>
                    <p class="mb-2"><strong>Tipo:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($training['type']) ?></span></p>
                    <p class="mb-2"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($training['training_date'])) ?></p>
                    <p class="mb-0"><strong>Duración:</strong> <?= htmlspecialchars($training['duration_hours'] ?: 'N/A') ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span>Colaboradores Registrados (<?= count($assignedUsers) ?>)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nombre</th>
                                    <th>Cédula / DNI</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignedUsers)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">No hay colaboradores registrados en esta carga.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assignedUsers as $u): ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold">
                                                <?= htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name_paternal'] . ' ' . $u['last_name_maternal'])) ?>
                                            </td>
                                            <td><?= htmlspecialchars($u['dni'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php if (!empty($u['attachment_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/uploads/trainings/<?= htmlspecialchars($u['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-file-earmark-pdf me-1"></i> Ver Adjunto
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin adjunto</span>
                                                <?php endif; ?>
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
    </div>
</div>
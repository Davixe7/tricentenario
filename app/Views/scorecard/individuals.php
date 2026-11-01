<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-none">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold text-brand-dark mb-0">Boletas / Individuales</h4>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/scorecard/individuals" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Seleccionar Colaborador / Despachador</label>
                <select name="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Seleccionar --</option>
                    <?php if (!empty($activeUsers)): ?>
                        <?php foreach ($activeUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($selectedUserId ?? '') == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(($u['employee_number'] ?? 'S/N') . ' - ' . $u['first_name'] . ' ' . $u['last_name_paternal']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($selectedUserScorecards)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-brand-dark text-white py-3">
            <h5 class="mb-0 fw-bold">Historial de Desempeño Individual</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Semestre</th>
                            <th>Mes</th>
                            <th class="text-center">Ventas (%)</th>
                            <th class="text-center">Asistencia Hábitos</th>
                            <th class="text-center">6 Pasos</th>
                            <th class="text-center">Faltas</th>
                            <th class="text-center">Aceites</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedUserScorecards as $sc): ?>
                            <tr>
                                <td><?= htmlspecialchars($sc['semester']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($sc['month_name']) ?></td>
                                <td class="text-center"><?= number_format($sc['sales'], 2) ?>%</td>
                                <td class="text-center"><?= number_format($sc['attendance_habits'], 2) ?></td>
                                <td class="text-center"><?= number_format($sc['six_steps'], 2) ?></td>
                                <td class="text-center"><?= (int)$sc['absences'] ?></td>
                                <td class="text-center"><?= (int)$sc['oils'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php elseif (!empty($selectedUserId)): ?>
    <div class="alert alert-warning shadow-sm">No hay registros de boletas individuales para este colaborador en el periodo cargado.</div>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<div class="container-fluid">
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/trainings" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Volver a Capacitaciones
        </a>
        <h4 class="fw-bold mt-2"><i class="bi bi-plus-circle me-2 text-primary"></i>Nueva Carga de Capacitación</h4>
    </div>

    <form action="<?= BASE_URL ?>/trainings/store" method="POST" id="trainingForm">
        <div class="row g-4">
            <!-- Datos Básicos de la Capacitación -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Información General
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Nombre de la Capacitación / Curso <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Ej. Primeros Auxilios y Brigadas" required>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="Curso">Curso</option>
                                <option value="Taller">Taller</option>
                                <option value="Certificación">Certificación</option>
                                <option value="Diplomado">Diplomado</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="training_date" class="form-label fw-semibold">Fecha de Impartición <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="training_date" name="training_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="duration_hours" class="form-label fw-semibold">Duración (Horas / Días)</label>
                            <input type="text" class="form-control" id="duration_hours" name="duration_hours" placeholder="Ej. 16 Horas o 2 Días">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selección Masiva de Colaboradores -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people me-1 text-primary"></i> Seleccionar Colaboradores Participantes</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" id="btnSelectAll">Seleccionar Todos</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">Deseleccionar</button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <input type="text" class="form-control form-control-sm" id="searchUser" placeholder="Buscar colaborador por nombre o DNI/Cédula...">
                        </div>

                        <div class="border rounded p-2 overflow-auto" style="max-height: 380px;">
                            <?php if (empty($users)): ?>
                                <p class="text-muted text-center py-3 mb-0">No hay usuarios disponibles.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush" id="userList">
                                    <?php foreach ($users as $user): ?>
                                        <?php 
                                            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name_paternal'] ?? '') . ' ' . ($user['last_name_maternal'] ?? ''));
                                            if (empty($fullName)) $fullName = $user['username'] ?? 'Usuario ' . $user['id'];
                                        ?>
                                        <label class="list-group-item d-flex align-items-center gap-3 user-item">
                                            <input class="form-check-input user-checkbox flex-shrink-0" type="checkbox" name="users[]" value="<?= $user['id'] ?>">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold user-name"><?= htmlspecialchars($fullName) ?></div>
                                                <small class="text-muted">DNI/Cédula: <?= htmlspecialchars($user['dni'] ?? 'N/A') ?></small>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end p-3">
                        <a href="<?= BASE_URL ?>/trainings" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Guardar Carga Masiva</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    const userItems = document.querySelectorAll('.user-item');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');

    // Filtro de búsqueda en vivo
    searchInput.addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        userItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    });

    // Botones de Selección
    btnSelectAll.addEventListener('click', function() {
        checkboxes.forEach(cb => {
            if (cb.closest('.user-item').style.display !== 'none') {
                cb.checked = true;
            }
        });
    });

    btnDeselectAll.addEventListener('click', function() {
        checkboxes.forEach(cb => cb.checked = false);
    });
});
</script>
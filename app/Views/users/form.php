<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold text-brand-dark mb-3">Configuración / Usuarios</h4>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4"><?= $user ? 'Editar Usuario' : 'Nuevo Usuario' ?></h5>

        <form action="<?= $user ? BASE_URL . '/users/update/' . $user['id'] : BASE_URL . '/users/store' ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" id="user_id" value="<?= $user['id'] ?? '' ?>">

            <!-- INFORMACIÓN LABORAL -->
            <h6 class="fw-bold text-brand-dark border-bottom pb-2 mb-3">INFORMACIÓN LABORAL</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">No. Empleado: <span class="text-danger">*</span></label>
                    <input type="number" name="employee_number" class="form-control" value="<?= htmlspecialchars($user['employee_number'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Nombre(s): <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Apellido Paterno: <span class="text-danger">*</span></label>
                    <input type="text" name="last_name_paternal" class="form-control" value="<?= htmlspecialchars($user['last_name_paternal'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Apellido Materno:</label>
                    <input type="text" name="last_name_maternal" class="form-control" value="<?= htmlspecialchars($user['last_name_maternal'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Puesto: <span class="text-danger">*</span></label>
                    <select name="position_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($positions as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (($user['position_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Área: <span class="text-danger">*</span></label>
                    <select name="area_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= (($user['area_id'] ?? '') == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jefe Inmediato: <span class="text-danger">*</span></label>
                    <select name="supervisor_id" class="form-select">
                        <option value="">-- Ninguno --</option>
                        <?php foreach ($activeUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($user['supervisor_id'] ?? '') == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name_paternal']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Usuario sistema: <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Contraseña: <?= $user ? '<span class="text-muted">(Opcional)</span>' : '<span class="text-danger">*</span>' ?></label>
                    <div class="input-group">
                        <input type="password" id="pwdInput" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rol (en sistema): <span class="text-danger">*</span></label>
                    <select name="role" id="roleSelect" class="form-select" onchange="checkRole()" required>
                        <option value="General Admin" <?= (($user['role'] ?? '') === 'General Admin') ? 'selected' : '' ?>>Administrador General</option>
                        <option value="Administrative Reporter" <?= (($user['role'] ?? '') === 'Administrative Reporter') ? 'selected' : '' ?>>Reportero Administrativo</option>
                        <option value="Operational Reporter" <?= (($user['role'] ?? '') === 'Operational Reporter') ? 'selected' : '' ?>>Reportero Operativo</option>
                        <option value="Collaborator" <?= (($user['role'] ?? '') === 'Collaborator') ? 'selected' : '' ?>>Colaborador</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Correo electrónico:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>

                <div class="col-md-4" id="counterpartDiv">
                    <label class="form-label fw-semibold">Reportero Contraparte:</label>
                    <select name="counterpart_reporter_id" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($activeUsers as$u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($user['counterpart_reporter_id'] ?? '') ==$u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' .$u['last_name_paternal']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Estatus: <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (($user['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option>
                        <option value="inactive" <?= (($user['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- INFORMACIÓN PERSONAL -->
            <h6 class="fw-bold text-brand-dark border-bottom pb-2 mb-3">INFORMACIÓN PERSONAL</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Teléfono: <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">NSS: <span class="text-danger">*</span></label>
                    <input type="text" name="nss" class="form-control" value="<?= htmlspecialchars($user['nss'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">R.F.C.:</label>
                    <input type="text" name="rfc" class="form-control text-uppercase" value="<?= htmlspecialchars($user['rfc'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">CURP:</label>
                    <input type="text" name="curp" id="curpInput" class="form-control text-uppercase" value="<?= htmlspecialchars($user['curp'] ?? '') ?>" maxlength="18">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Nacimiento:</label>
                    <input type="date" name="birth_date" id="birthDateInput" class="form-control" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Dirección:</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fotografía de perfil:</label>
                    <input type="file" name="avatar" class="form-control" accept="image/*">
                </div>
            </div>

            <!-- DOCUMENTOS OFICIALES -->
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-brand-dark mb-0">DOCUMENTOS OFICIALES</h6>
                <?php if ($user): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                        <i class="bi bi-paperclip me-1"></i> Adjuntar Documento
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Descripción del Documento</th>
                            <th class="text-center" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as$doc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doc['description']) ?></td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL . '/' . $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="Ver archivo">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL . '/' . $doc['file_path'] ?>" download class="btn btn-sm btn-outline-primary me-1" title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/users/delete-document/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar documento?')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    <?= $user ? 'No hay documentos adjuntos.' : 'Guarde el usuario para habilitar la subida de documentos.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TRAYECTORIA -->
            <h6 class="fw-bold text-brand-dark border-bottom pb-2 mb-3">TRAYECTORIA</h6>

            <?php if ($user): ?>
                <!-- Entrada de trayectoria -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 25%;">Movimiento</th>
                                <th style="width: 20%;">Fecha</th>
                                <th style="width: 20%;">Recontratable</th>
                                <th style="width: 30%;">Observaciones</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select id="trayect_movement" class="form-select">
                                        <option value="Ingreso">Ingreso</option>
                                        <option value="Baja">Baja</option>
                                        <option value="Re-ingreso">Re-ingreso</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="date" id="trayect_date" class="form-control text-center">
                                </td>
                                <td>
                                    <select id="trayect_rehireable" class="form-select text-center">
                                        <option value="Si">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" id="trayect_notes" class="form-control" placeholder="Observaciones...">
                                </td>
                                <td class="text-center">
                                    <button type="button" id="btnAddTrayectoria" class="btn btn-primary btn-sm" title="Agregar trayectoria">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Tabla de trayectoria registrada -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;" class="text-center"></th>
                            <th style="width: 25%;">Movimiento</th>
                            <th style="width: 20%;" class="text-center">Fecha</th>
                            <th style="width: 20%;" class="text-center">Recontratable</th>
                            <th style="width: 30%;">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($trajectories)): ?>
                            <?php foreach ($trajectories as$tray): ?>
                                <tr>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/users/delete-trajectory/<?= $tray['id'] ?>" class="text-danger" onclick="return confirm('¿Eliminar registro?')">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </a>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($tray['movement']) ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($tray['movement_date'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $tray['rehireable'] === 'Si' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $tray['rehireable'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($tray['notes'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <?= $user ? 'No hay movimientos de trayectoria registrados.' : 'Guarde el usuario para registrar trayectoria.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/users" class="btn btn-light border">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<?php if ($user): ?>
<!-- MODAL ADJUNTAR DOCUMENTO -->
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/upload-document" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Adjuntar Documento Oficial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción del documento</label>
                    <input type="text" name="description" class="form-control" placeholder="Ej: Acta de Nacimiento" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Archivo (PDF, PNG, JPG)</label>
                    <input type="file" name="document" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir Documento</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function togglePassword() {
    const pwd = document.getElementById('pwdInput');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
}

function checkRole() {
    const role = document.getElementById('roleSelect').value;
    const div = document.getElementById('counterpartDiv');
    if (role === 'Administrative Reporter' || role === 'Operational Reporter') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}
checkRole();

document.addEventListener('DOMContentLoaded', function() {
    // Autocompletar Fecha de Nacimiento desde la CURP
    const curpInput = document.getElementById('curpInput');
    const birthDateInput = document.getElementById('birthDateInput');

    if (curpInput && birthDateInput) {
        curpInput.addEventListener('input', function() {
            const curp = this.value.trim().toUpperCase();
            if (curp.length >= 10) {
                const yearDigits = curp.substring(4, 6);
                const month = curp.substring(6, 8);
                const day = curp.substring(8, 10);
                
                const currentTwoDigits = new Date().getFullYear().toString().substring(2);
                const century = (parseInt(yearDigits) > parseInt(currentTwoDigits)) ? '19' : '20';
                
                birthDateInput.value = `${century}${yearDigits}-${month}-${day}`;
            }
        });
    }

    // Guardar Trayectoria vía AJAX
    const btnAddTrayectoria = document.getElementById('btnAddTrayectoria');
    if (btnAddTrayectoria) {
        btnAddTrayectoria.addEventListener('click', function() {
            const userId = document.getElementById('user_id').value;
            const movement = document.getElementById('trayect_movement').value;
            const date = document.getElementById('trayect_date').value;
            const rehireable = document.getElementById('trayect_rehireable').value;
            const notes = document.getElementById('trayect_notes').value;

            if (!date) {
                alert('Ingresa una fecha válida');
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', '<?= CSRF::generateToken() ?>');
            formData.append('user_id', userId);
            formData.append('movement', movement);
            formData.append('movement_date', date);
            formData.append('rehireable', rehireable);
            formData.append('notes', notes);

            fetch('<?= BASE_URL ?>/users/add-trajectory', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ocurrió un error al guardar la trayectoria');
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
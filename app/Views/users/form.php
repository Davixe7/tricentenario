<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h4 class="fw-bold text-brand-dark mb-3">Configuración / Usuarios</h4>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4"><?= $user ? 'Editar Usuario' : 'Nuevo Usuario' ?></h5>

        <form action="<?= $user ? BASE_URL . '/users/update/' . $user['id'] : BASE_URL . '/users/store' ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

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
                        <?php foreach ($activeUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($user['counterpart_reporter_id'] ?? '') == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name_paternal']) ?></option>
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
                    <input type="text" name="rfc" class="form-control" value="<?= htmlspecialchars($user['rfc'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">CURP:</label>
                    <input type="text" name="curp" class="form-control" value="<?= htmlspecialchars($user['curp'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha Nacimiento:</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>">
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

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>/users" class="btn btn-light border">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

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
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
<?php 
require_once __DIR__ . '/../layout/header.php'; 
$csrfToken = CSRF::generateToken();
$old = $oldInput ?? [];
?>

<style>
/* Estilos para el diseño reticular de formulario */
.form-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #dce5ef;
}
.form-table td {
    padding: 6px 12px;
    vertical-align: middle;
    border: 1px solid #dce5ef;
}
.form-table td.label-col {
    background-color: #eef6fc;
    font-weight: 600;
    width: 35%;
    color: #212529;
    font-size: 0.9rem;
}
.form-table td.input-col {
    background-color: #ffffff;
}
.form-table .form-control, 
.form-table .form-select {
    border-radius: 4px;
    font-size: 0.9rem;
}

/* Contenedor Drag & Drop para Fotografía */
.drop-zone {
    border: 2px dashed #0d6efd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    background-color: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.drop-zone:hover, .drop-zone.dragover {
    background-color: #eef6fc;
    border-color: #0b5ed7;
}
.avatar-preview-img {
    max-width: 150px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* Estilos de distinción visual para secciones en tarjetas */
.section-card {
    border: 1px solid #dce5ef;
    border-radius: 8px;
    background-color: #ffffff;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
}
.section-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #dce5ef;
    padding: 12px 18px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-body {
    padding: 18px;
}
</style>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="fw-bold text-brand-dark mb-0">Configuración / Usuarios</h4>
</div>

<!-- Navegación por pestañas superiores -->
<div class="mb-3">
    <a href="<?= BASE_URL ?>/users" class="btn btn-primary me-1">Lista de Usuarios</a>
    <span class="btn btn-light border fw-bold active"><?= $user ? 'Editar Usuario' : 'Nuevo Usuario' ?></span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <!-- MENSAJES FLASH / NOTIFICACIONES -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="<?= $user ? BASE_URL . '/users/update/' . $user['id'] : BASE_URL . '/users/store' ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" id="user_id" value="<?= $user['id'] ?? '' ?>">

            <!-- INFORMACIÓN LABORAL -->
            <h6 class="fw-bold text-brand-dark border-bottom pb-2 mb-3">INFORMACIÓN LABORAL</h6>
            <div class="row g-4 mb-4">
                <!-- Columna Izquierda: Formulario en Tabla -->
                <div class="col-lg-7">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <td class="label-col">No. Empleado: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="number" step="1" name="employee_number" class="form-control form-control-sm" value="<?= htmlspecialchars($old['employee_number'] ?? $user['employee_number'] ?? '') ?>" required placeholder="Campo numérico sin decimales">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Nombre(s): <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="text" name="first_name" class="form-control form-control-sm" value="<?= htmlspecialchars($old['first_name'] ?? $user['first_name'] ?? '') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Apellido Paterno: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="text" name="last_name_paternal" class="form-control form-control-sm" value="<?= htmlspecialchars($old['last_name_paternal'] ?? $user['last_name_paternal'] ?? '') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Apellido Materno:</td>
                                <td class="input-col">
                                    <input type="text" name="last_name_maternal" class="form-control form-control-sm" value="<?= htmlspecialchars($old['last_name_maternal'] ?? $user['last_name_maternal'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Puesto: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <select name="position_id" class="form-select form-select-sm" required>
                                        <option value="">-- Seleccionar Puesto --</option>
                                        <?php 
                                            $valPos = $old['position_id'] ?? $user['position_id'] ?? '';
                                            foreach ($positions as $p): 
                                        ?>
                                            <option value="<?= $p['id'] ?>" <?= ($valPos == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Área: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <select name="area_id" class="form-select form-select-sm" required>
                                        <option value="">-- Seleccionar Área --</option>
                                        <?php 
                                            $valArea = $old['area_id'] ?? $user['area_id'] ?? '';
                                            foreach ($areas as $a): 
                                        ?>
                                            <option value="<?= $a['id'] ?>" <?= ($valArea == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Jefe Inmediato: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <select name="supervisor_id" class="form-select form-select-sm">
                                        <option value="">-- Ninguno --</option>
                                        <?php 
                                            $valSup = $old['supervisor_id'] ?? $user['supervisor_id'] ?? '';
                                            foreach ($activeUsers as $u): 
                                        ?>
                                            <option value="<?= $u['id'] ?>" <?= ($valSup == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name_paternal']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Usuario sistema: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($old['username'] ?? $user['username'] ?? '') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Contraseña: <?= $user ? '<span class="text-muted">(Opcional)</span>' : '<span class="text-danger">*</span>' ?></td>
                                <td class="input-col">
                                    <div class="input-group input-group-sm">
                                        <input type="password" id="pwdInput" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()"><i class="bi bi-eye"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Rol (en sistema): <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <?php $valRole = $old['role'] ?? $user['role'] ?? ''; ?>
                                    <select name="role" id="roleSelect" class="form-select form-select-sm" onchange="checkRole()" required>
                                        <option value="General Admin" <?= ($valRole === 'General Admin') ? 'selected' : '' ?>>Administrador General</option>
                                        <option value="Administrative Reporter" <?= ($valRole === 'Administrative Reporter') ? 'selected' : '' ?>>Reportero Administrativo</option>
                                        <option value="Operational Reporter" <?= ($valRole === 'Operational Reporter') ? 'selected' : '' ?>>Reportero Operativo</option>
                                        <option value="Collaborator" <?= ($valRole === 'Collaborator') ? 'selected' : '' ?>>Colaborador</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Correo electrónico:</td>
                                <td class="input-col">
                                    <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($old['email'] ?? $user['email'] ?? '') ?>" placeholder="Opcional">
                                </td>
                            </tr>
                            <tr id="counterpartTr">
                                <td class="label-col">Reportero Contraparte: <span id="counterpartAsterisk" class="text-danger" style="display:none;">*</span></td>
                                <td class="input-col">
                                    <?php $valCount = $old['counterpart_reporter_id'] ?? $user['counterpart_reporter_id'] ?? ''; ?>
                                    <select name="counterpart_reporter_id" id="counterpartSelect" class="form-select form-select-sm">
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach ($counterpartUsers ?? $activeUsers as $u): ?>
                                            <option value="<?= $u['id'] ?>" <?= ($valCount == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name_paternal']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Estatus: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <?php $valStatus = $old['status'] ?? $user['status'] ?? 'active'; ?>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active" <?= ($valStatus === 'active') ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactive" <?= ($valStatus === 'inactive') ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Columna Derecha: Subida Drag & Drop e Imagen -->
                <div class="col-lg-5 d-flex flex-column align-items-center">
                    <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/png, image/jpeg">
                    
                    <div class="drop-zone w-100 mb-2" id="dropZone">
                        <i class="bi bi-cloud-arrow-up display-5 text-primary mb-2"></i>
                        <p class="mb-1 fw-bold text-secondary">Arrastra o haz click aquí para subir imagen.</p>
                    </div>
                    
                    <div class="text-muted small text-center mb-3">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Tamaño sugerido: 11 cm x 11 cm. Peso máximo: 2MB. Formatos aceptados: <strong>JPG y PNG</strong>.
                    </div>

                    <div id="avatarPreviewContainer" class="text-center mt-2">
                        <?php 
                            $avatarSrc = !empty($user['avatar']) 
                                ? BASE_URL . '/uploads/' . $user['avatar'] 
                                : BASE_URL . '/assets/img/default-avatar.png';
                        ?>
                        <img id="avatarPreview" src="<?= $avatarSrc ?>" alt="Vista previa" class="avatar-preview-img shadow-sm" onerror="this.src='https://via.placeholder.com/150?text=Sin+Imagen'">
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN PERSONAL -->
            <h6 class="fw-bold text-brand-dark border-bottom pb-2 mb-3">INFORMACIÓN PERSONAL</h6>
            <div class="row mb-4">
                <div class="col-lg-7">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <td class="label-col">Teléfono: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">NSS: <span class="text-danger">*</span></td>
                                <td class="input-col">
                                    <input type="text" name="nss" class="form-control form-control-sm" value="<?= htmlspecialchars($old['nss'] ?? $user['nss'] ?? '') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">R.F.C.:</td>
                                <td class="input-col">
                                    <input type="text" name="rfc" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($old['rfc'] ?? $user['rfc'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">CURP:</td>
                                <td class="input-col">
                                    <input type="text" name="curp" id="curpInput" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($old['curp'] ?? $user['curp'] ?? '') ?>" maxlength="18">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Fecha Nacimiento:</td>
                                <td class="input-col">
                                    <input type="date" name="birth_date" id="birthDateInput" class="form-control form-control-sm" value="<?= htmlspecialchars($old['birth_date'] ?? $user['birth_date'] ?? '') ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Dirección:</td>
                                <td class="input-col">
                                    <textarea name="address" class="form-control form-control-sm" rows="3" placeholder="Campo de texto amplio"><?= htmlspecialchars($old['address'] ?? $user['address'] ?? '') ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DOCUMENTOS OFICIALES -->
            <div class="section-card">
                <div class="section-header">
                    <span><i class="bi bi-file-earmark-text me-2 text-primary"></i> DOCUMENTOS OFICIALES</span>
                    <?php if ($user): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                            <i class="bi bi-paperclip me-1"></i> Adjuntar
                        </button>
                    <?php endif; ?>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción del Documento</th>
                                    <th class="text-center" style="width: 160px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($documents)): ?>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($doc['description']) ?></td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL . '/' . $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-link text-primary p-1" title="Ver" data-bs-toggle="tooltip">
                                                    <i class="bi bi-eye fs-5"></i>
                                                </a>
                                                <a href="<?= BASE_URL . '/' . $doc['file_path'] ?>" download class="btn btn-sm btn-link text-success p-1" title="Descargar" data-bs-toggle="tooltip">
                                                    <i class="bi bi-download fs-5"></i>
                                                </a>
                                                <form method="POST" action="<?= BASE_URL ?>/users/delete-document/<?= $doc['id'] ?>" class="d-inline" onsubmit="return confirm('¿Eliminar documento?')">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Eliminar" data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash fs-5"></i>
                                                    </button>
                                                </form>
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
                </div>
            </div>

            <!-- HISTORIAL DE CAPACITACIONES -->
            <div class="section-card">
                <div class="section-header">
                    <span><i class="bi bi-easel me-2 text-primary"></i> HISTORIAL DE CAPACITACIONES</span>
                    <?php if ($user): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAsignarCapacitacion">
                            <i class="bi bi-plus-lg me-1"></i> Asignar Capacitación
                        </button>
                    <?php endif; ?>
                </div>
                <div class="section-body p-0">
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Capacitación</th>
                                    <th class="text-center" style="width: 150px;">Tipo</th>
                                    <th class="text-center" style="width: 130px;">Fecha</th>
                                    <th class="text-center" style="width: 130px;">Duración (hrs)</th>
                                    <th class="text-center" style="width: 200px;">Adjuntos / Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($userTrainings)): ?>
                                    <?php foreach ($userTrainings as $ut): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($ut['title']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= htmlspecialchars($ut['type']) ?></span>
                                            </td>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($ut['training_date'])) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($ut['duration_hours'] ?: 'N/A') ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($ut['attachment_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/uploads/trainings/<?= htmlspecialchars($ut['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-link text-primary p-1" title="Ver" data-bs-toggle="tooltip">
                                                        <i class="bi bi-eye fs-5"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/uploads/trainings/<?= htmlspecialchars($ut['attachment_path']) ?>" download class="btn btn-sm btn-link text-success p-1" title="Descargar" data-bs-toggle="tooltip">
                                                        <i class="bi bi-download fs-5"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-link text-info p-1" title="Adjuntar" onclick="openUploadModal(<?= $ut['user_training_id'] ?>)" data-bs-toggle="tooltip">
                                                    <i class="bi bi-paperclip fs-5"></i>
                                                </button>

                                                <form method="POST" action="<?= BASE_URL ?>/users/delete-training" class="d-inline" onsubmit="return confirm('¿Quitar esta capacitación del colaborador?')">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="user_training_id" value="<?= $ut['user_training_id'] ?>">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Eliminar" data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash fs-5"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            <?= $user ? 'No hay capacitaciones asignadas.' : 'Guarde el usuario para registrar capacitaciones.' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN ADICIONAL -->
            <div class="section-card">
                <div class="section-header">
                    <span><i class="bi bi-info-circle me-2 text-primary"></i> INFORMACIÓN ADICIONAL</span>
                </div>
                <div class="section-body">
                    <?php if ($user): ?>
                        <!-- Entrada de Información Adicional -->
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th style="width: 30%;">Fecha</th>
                                        <th style="width: 65%;">Descripción de lo Sucendido</th>
                                        <th style="width: 5%;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="date" id="additional_date" class="form-control form-control-sm text-center">
                                        </td>
                                        <td>
                                            <select id="additional_description_select" class="form-select form-select-sm mb-2" onchange="checkAdditionalDesc(this)">
                                                <option value="">-- Seleccionar Descripción --</option>
                                                <option value="Incidencia">Incidencia</option>
                                                <option value="Permiso">Permiso</option>
                                                <option value="Falta">Falta</option>
                                                <option value="Reconocimiento">Reconocimiento</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                            <input type="text" id="additional_description_input" class="form-control form-control-sm" placeholder="Especifique la descripción..." style="display: none;">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" id="btnAddAdditional" class="btn btn-primary btn-sm" title="Agregar información adicional">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Tabla de Información Adicional Registrada -->
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;" class="text-center">Fecha</th>
                                    <th style="width: 55%;">Descripción de lo Sucendido</th>
                                    <th style="width: 25%;" class="text-center">Adjuntos / Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($additionalInfos)): ?>
                                    <?php foreach ($additionalInfos as $info): ?>
                                        <tr>
                                            <td class="text-center"><?= !empty($info['info_date']) ? date('d/m/Y', strtotime($info['info_date'])) : '' ?></td>
                                            <td><?= htmlspecialchars($info['description'] ?? '') ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($info['attachment_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/uploads/additionals/<?= htmlspecialchars($info['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-link text-primary p-1" title="Ver" data-bs-toggle="tooltip">
                                                        <i class="bi bi-eye fs-5"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>/uploads/additionals/<?= htmlspecialchars($info['attachment_path']) ?>" download class="btn btn-sm btn-link text-success p-1" title="Descargar" data-bs-toggle="tooltip">
                                                        <i class="bi bi-download fs-5"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-link text-info p-1" title="Adjuntar" onclick="openAdditionalUploadModal(<?= $info['id'] ?>)" data-bs-toggle="tooltip">
                                                    <i class="bi bi-paperclip fs-5"></i>
                                                </button>

                                                <form method="POST" action="<?= BASE_URL ?>/users/delete-additional-info/<?= $info['id'] ?>" class="d-inline" onsubmit="return confirm('¿Eliminar registro?')">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Eliminar" data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash fs-5"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            <?= $user ? 'No hay información adicional registrada.' : 'Guarde el usuario para registrar información adicional.' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TRAYECTORIA -->
            <div class="section-card">
                <div class="section-header">
                    <span><i class="bi bi-signpost-split me-2 text-primary"></i> TRAYECTORIA</span>
                </div>
                <div class="section-body">
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
                                        <th style="width: 5%;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select id="trayect_movement" class="form-select form-select-sm">
                                                <option value="Ingreso">Ingreso</option>
                                                <option value="Baja">Baja</option>
                                                <option value="Re-ingreso">Re-ingreso</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date" id="trayect_date" class="form-control form-control-sm text-center">
                                        </td>
                                        <td>
                                            <select id="trayect_rehireable" class="form-select form-select-sm text-center">
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" id="trayect_notes" class="form-control form-control-sm" placeholder="Observaciones...">
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
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Movimiento</th>
                                    <th style="width: 20%;" class="text-center">Fecha</th>
                                    <th style="width: 15%;" class="text-center">Recontratable</th>
                                    <th style="width: 30%;">Observaciones</th>
                                    <th style="width: 10%;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($trajectories)): ?>
                                    <?php foreach ($trajectories as $tray): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($tray['movement'] ?? '') ?></td>
                                            <td class="text-center"><?= !empty($tray['movement_date']) ? date('d/m/Y', strtotime($tray['movement_date'])) : '' ?></td>
                                            <td class="text-center">
                                                <?php 
                                                    $rehireVal = trim($tray['rehireable'] ?? '');
                                                    $isYes = (strcasecmp($rehireVal, 'Si') === 0 || strcasecmp($rehireVal, 'Sí') === 0 || strcasecmp($rehireVal, 'SI') === 0 || $rehireVal === '1');
                                                ?>
                                                <span class="badge <?= $isYes ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= htmlspecialchars($tray['rehireable'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($tray['notes'] ?? '') ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($tray['id'])): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/users/delete-trajectory/<?= $tray['id'] ?>" class="d-inline" onsubmit="return confirm('¿Eliminar registro de trayectoria?')">
                                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Eliminar" data-bs-toggle="tooltip">
                                                            <i class="bi bi-trash fs-5"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin ID</span>
                                                <?php endif; ?>
                                            </td>
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
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= BASE_URL ?>/users" class="btn btn-light border">Cancelar</a>
                <button type="submit" class="btn btn-success px-4">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<?php if ($user): ?>
<!-- MODAL ADJUNTAR DOCUMENTO OFICIAL -->
<div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/upload-document" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
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

<!-- MODAL ASIGNAR O CREAR CAPACITACIÓN -->
<div class="modal fade" id="modalAsignarCapacitacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-brand-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-easel me-2"></i>Asignar Capacitación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Pestañas del Modal -->
                <ul class="nav nav-pills mb-3" id="trainingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="select-tab" data-bs-toggle="pill" data-bs-target="#tab-select" type="button" role="tab">Seleccionar Existente</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="create-tab" data-bs-toggle="pill" data-bs-target="#tab-create" type="button" role="tab">Crear Nueva Capacitación</button>
                    </li>
                </ul>

                <div class="tab-content" id="trainingTabsContent">
                    <!-- Tab 1: Seleccionar Existente -->
                    <div class="tab-pane fade show active" id="tab-select" role="tabpanel">
                        <form action="<?= BASE_URL ?>/users/add-training" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Seleccionar Capacitación del Catálogo</label>
                                <select name="training_id" class="form-select" required>
                                    <option value="">-- Seleccionar Capacitación --</option>
                                    <?php if (!empty($allTrainings)): ?>
                                        <?php foreach ($allTrainings as $tr): ?>
                                            <option value="<?= $tr['id'] ?>">
                                                <?= htmlspecialchars($tr['title']) ?> (<?= date('d/m/Y', strtotime($tr['training_date'])) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Asignar Seleccionada</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Crear Nueva Capacitación y Asignar -->
                    <div class="tab-pane fade" id="tab-create" role="tabpanel">
                        <form action="<?= BASE_URL ?>/users/create-and-assign-training" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre de la Capacitación / Curso <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="Ej. Curso Primeros Auxilios">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="Certificación">Certificación</option>
                                        <option value="Curso" selected>Curso</option>
                                        <option value="Diplomado">Diplomado</option>
                                        <option value="Taller">Taller</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="training_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Duración</label>
                                    <input type="text" name="duration_hours" class="form-control" placeholder="Ej. 8 hrs">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Comprobante / Documento Adjunto (Opcional)</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Crear y Asignar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ADJUNTAR COMPROBANTE DE CAPACITACIÓN -->
<div class="modal fade" id="modalUploadTrainingDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/upload-training-attachment" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <input type="hidden" name="user_training_id" id="modal_user_training_id" value="">
            <div class="modal-header bg-brand-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-paperclip me-2"></i>Adjuntar Comprobante de Capacitación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Archivo / Constancia (PDF, JPG, PNG)</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-cloud-upload me-1"></i> Subir Archivo</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ADJUNTAR COMPROBANTE DE INFORMACIÓN ADICIONAL -->
<div class="modal fade" id="modalUploadAdditionalDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= BASE_URL ?>/users/upload-additional-info-attachment" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <input type="hidden" name="additional_info_id" id="modal_additional_info_id" value="">
            <div class="modal-header bg-brand-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-paperclip me-2"></i>Adjuntar Documento a Información Adicional</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Archivo (PDF, JPG, PNG)</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-cloud-upload me-1"></i> Subir Archivo</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openUploadModal(userTrainingId) {
    document.getElementById('modal_user_training_id').value = userTrainingId;
    var modal = new bootstrap.Modal(document.getElementById('modalUploadTrainingDoc'));
    modal.show();
}

function openAdditionalUploadModal(additionalInfoId) {
    document.getElementById('modal_additional_info_id').value = additionalInfoId;
    var modal = new bootstrap.Modal(document.getElementById('modalUploadAdditionalDoc'));
    modal.show();
}

function checkAdditionalDesc(select) {
    const input = document.getElementById('additional_description_input');
    if (select.value === 'Otro') {
        input.style.display = 'block';
        input.focus();
    } else {
        input.style.display = 'none';
        input.value = '';
    }
}

function togglePassword() {
    const pwd = document.getElementById('pwdInput');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
}

function checkRole() {
    const role = document.getElementById('roleSelect').value;
    const tr = document.getElementById('counterpartTr');
    if (role === 'Administrative Reporter' || role === 'Operational Reporter') {
        tr.style.display = 'table-row';
    } else {
        tr.style.display = 'none';
    }
}
checkRole();

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // --- LÓGICA DRAG AND DROP FOTOGRAFÍA ---
    const dropZone = document.getElementById('dropZone');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (dropZone && avatarInput) {
        dropZone.addEventListener('click', () => avatarInput.click());

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                avatarInput.files = files;
                handleFiles(files[0]);
            }
        });

        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFiles(this.files[0]);
            }
        });

        function handleFiles(file) {
            if (!file.type.startsWith('image/')) {
                alert('Por favor selecciona un archivo de imagen válido (JPG o PNG).');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // --- AUTOCOMPLETAR FECHA DE NACIMIENTO DESDE LA CURP O EDICIÓN MANUAL ---
    const curpInput = document.getElementById('curpInput');
    const birthDateInput = document.getElementById('birthDateInput');

    if (curpInput && birthDateInput) {
        function updateBirthDateFromCURP() {
            const curp = curpInput.value.trim().toUpperCase();
            if (curp.length >= 10) {
                const yearDigits = curp.substring(4, 6);
                const month = curp.substring(6, 8);
                const day = curp.substring(8, 10);
                
                const currentTwoDigits = new Date().getFullYear().toString().substring(2);
                const century = (parseInt(yearDigits) > parseInt(currentTwoDigits)) ? '19' : '20';
                
                birthDateInput.value = `${century}${yearDigits}-${month}-${day}`;
                birthDateInput.readOnly = true;
                birthDateInput.classList.add('bg-light');
            } else {
                birthDateInput.readOnly = false;
                birthDateInput.classList.remove('bg-light');
            }
        }

        curpInput.addEventListener('input', updateBirthDateFromCURP);
        updateBirthDateFromCURP();
    }

    // --- GUARDAR INFORMACIÓN ADICIONAL VÍA AJAX ---
    const btnAddAdditional = document.getElementById('btnAddAdditional');
    if (btnAddAdditional) {
        btnAddAdditional.addEventListener('click', function() {
            const userId = document.getElementById('user_id').value;
            const infoDate = document.getElementById('additional_date').value;
            const selectVal = document.getElementById('additional_description_select').value;
            
            let description = selectVal;
            if (selectVal === 'Otro') {
                description = document.getElementById('additional_description_input').value.trim();
            }

            if (!infoDate || !description) {
                alert('Por favor ingresa la fecha y especifica la descripción.');
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrfToken ?>');
            formData.append('user_id', userId);
            formData.append('info_date', infoDate);
            formData.append('description', description);

            fetch('<?= BASE_URL ?>/users/add-additional-info', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ocurrió un error al guardar la información adicional.');
                }
            });
        });
    }

    // --- GUARDAR TRAYECTORIA VÍA AJAX ---
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
            formData.append('csrf_token', '<?= $csrfToken ?>');
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
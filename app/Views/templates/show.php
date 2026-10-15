<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="fw-bold text-brand-dark mb-0">Plantillas / <?= htmlspecialchars($config['title']) ?></h4>
</div>

<!-- Alerta de Notificación / Éxito -->
<?php if (isset($_GET['success']) && isset($_GET['count'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <strong>Se han actualizado <?= (int)$_GET['count'] ?> registros.</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'semester_required'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <strong>Es obligatorio elegir uno de los dos semestres.</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light border-0 py-3">
        <span class="btn btn-light bg-white border fw-bold text-dark pe-none">
            <?= htmlspecialchars($config['tab_title']) ?>
        </span>
    </div>
    <div class="card-body p-4">

        <!-- SECCIÓN INSTRUCCIONES -->
        <h6 class="fw-bold text-uppercase text-dark mb-3">INSTRUCCIONES</h6>
        <div class="d-flex align-items-start gap-3 mb-4">
            <i class="bi bi-info-circle-fill text-danger fs-4 mt-1"></i>
            <ol class="ps-3 mb-0 text-secondary" style="line-height: 1.6;">
                <li>Revisa los datos de la última actualización.</li>
                <li>Descarga la plantilla del Excel.</li>
                <li>Llena la información de cada columna con las siguientes consideraciones:
                    <ul class="mt-1">
                        <li>Respeta el formato sin agregar ni quitar filas o columnas.</li>
                        <li>Sólo captura valores, no utilices fórmulas en ninguna celda del Excel.</li>
                        <?php if ($config['date_format_note']): ?>
                            <li>El formato de fecha debe ser <strong>DD/MM/AAAA</strong>.</li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ol>
        </div>

        <!-- SECCIÓN ÚLTIMA ACTUALIZACIÓN -->
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase text-dark d-inline me-2">ÚLTIMA ACTUALIZACIÓN:</h6>
            <?php if (!empty($lastLog)): ?>
                <span class="badge bg-warning text-dark fs-6 font-monospace px-3 py-2">
                    <?= date('d/m/Y H:i', strtotime($lastLog['created_at'])) ?> hrs.
                </span>
            <?php else: ?>
                <span class="badge bg-secondary fs-6 px-3 py-2">
                    Sin actualizaciones registradas
                </span>
            <?php endif; ?>
        </div>

        <!-- SECCIÓN DESCARGAR PLANTILLA -->
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase text-dark mb-3">DESCARGAR PLANTILLA</h6>
            <a href="<?= BASE_URL ?>/templates/download/<?= $slug ?>" class="btn btn-link text-primary text-decoration-none p-0 fw-semibold fs-6">
                <i class="bi bi-download fs-4 align-middle me-2"></i> Descargar plantilla
            </a>
        </div>

        <!-- SECCIÓN CARGAR INFORMACIÓN (EL FORMULARIO ENVUELVE TODO CORRECTAMENTE) -->
        <form action="<?= BASE_URL ?>/templates/upload" method="POST" enctype="multipart/form-data" id="uploadTemplateForm">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
            <input type="hidden" name="template_slug" value="<?= $slug ?>">

            <h6 class="fw-bold text-uppercase text-dark mb-3">CARGAR INFORMACIÓN</h6>

            <div class="d-flex align-items-center flex-wrap gap-4 mb-4">
                <?php if ($config['has_semester']): ?>
                    <div class="d-flex align-items-center gap-3 me-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="semester" id="sem1" value="1" required>
                            <label class="form-check-label fw-semibold" for="sem1">Semestre 1</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="semester" id="sem2" value="2" required>
                            <label class="form-check-label fw-semibold" for="sem2">Semestre 2</label>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Input Oculto de Archivo Excel -->
                <input type="file" name="excel_file" id="excelFileInput" class="d-none" accept=".xlsx, .xls" required>

                <!-- Botón Adjuntar -->
                <button type="button" class="btn btn-link text-primary text-decoration-none p-0 fw-semibold fs-6" id="btnAttach">
                    <i class="bi bi-paperclip fs-4 align-middle me-1"></i> Adjuntar
                </button>

                <!-- Área de vista previa del archivo cargado -->
                <div id="filePreview" class="d-none align-items-center gap-2 bg-light border rounded px-3 py-1">
                    <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                    <span id="fileName" class="small fw-bold text-dark"></span>
                    <button type="button" class="btn-close btn-sm ms-2" id="btnRemoveFile" title="Eliminar archivo"></button>
                </div>
            </div>

            <!-- Botón Guardar (AHORA DENTRO DEL FORMULARIO) -->
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-success px-5 py-2 fw-bold fs-5 shadow-sm" style="background-color: #28a745; border: none;">
                    Guardar
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAttach = document.getElementById('btnAttach');
    const excelFileInput = document.getElementById('excelFileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const btnRemoveFile = document.getElementById('btnRemoveFile');

    if (btnAttach && excelFileInput) {
        btnAttach.addEventListener('click', () => excelFileInput.click());

        excelFileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileName.textContent = this.files[0].name;
                filePreview.classList.remove('d-none');
                filePreview.classList.add('d-flex');
            }
        });

        btnRemoveFile.addEventListener('click', function() {
            excelFileInput.value = '';
            filePreview.classList.remove('d-flex');
            filePreview.classList.add('d-none');
        });
    }
});
</script>
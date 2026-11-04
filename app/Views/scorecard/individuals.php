<style>
/* Permitir que los dropdowns del header no se corten en esta vista */
.card, .card-body, .table-responsive, main, .container-fluid {
    overflow: visible !important;
}

@media print {
    /* Ocultar elementos de la interfaz web que no van en el PDF */
    nav, header, sidebar, .sidebar, #sidebar, .navbar, 
    .btn, a.btn, button, .no-print,
    .card-header .d-flex form, #semesterSelectInd {
        display: none !important;
    }

    /* Configurar página horizontal con márgenes mínimos para que entre todo */
    @page {
        size: landscape;
        margin: 5mm;
    }

    body {
        background-color: #fff !important;
        font-size: 9px !important;
        color: #000 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .container, .container-fluid, main {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Reducir espaciados y tarjetas para que quepan en 1 sola hoja */
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
        margin-bottom: 5px !important;
        page-break-inside: avoid !important;
    }

    .card-body {
        padding: 5px !important;
    }

    .card-header {
        background-color: #0d6efd !important;
        color: #fff !important;
        padding: 4px !important;
        font-size: 10px !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Forzar que la tabla y sus 13 columnas quepan sin desbordarse */
    .table-responsive {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        font-size: 8.5px !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
    }

    th, td {
        padding: 3px 2px !important;
        border: 1px solid #bbb !important;
        word-wrap: break-word !important;
        overflow: hidden !important;
    }

    /* Convertir inputs editables en texto limpio sin bordes */
    input.form-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        text-align: center !important;
        font-weight: bold !important;
        font-size: 8.5px !important;
        width: 100% !important;
    }

    /* Preservar colores y semáforos */
    .bg-success, .bg-warning, .bg-danger, .bg-primary, .bg-dark, .table-primary, .table-warning {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .row {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 5px !important;
    }

    .col-md-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }
}
</style>

<div class="d-flex align-items-center justify-content-between mb-3 no-print">
    <h4 class="fw-bold text-brand-dark mb-0">
        <?= $selectedUser ? 'Boleta / Boleta Individual' : 'Boleta / Boleta Individual' ?>
    </h4>
    <?php if ($selectedUser): ?>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/scorecard/individuals?status=<?= urlencode($statusFilter) ?>&limit=<?= urlencode($limit) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
            </a>
            <button type="button" onclick="printScorecardPDF()" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Imprimir PDF
            </button>
            <button type="submit" form="individualScorecardForm" class="btn btn-sm btn-success px-3">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if (!$selectedUser): ?>
    <!-- 1. VISTA ÍNDICE / LISTADO DE DESPACHADORES -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="fw-bold text-brand-dark mb-0">Boleta Individual</h5>
                
                <form method="GET" action="<?= BASE_URL ?>/scorecard/individuals" class="d-flex flex-wrap align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="limitSelect" class="fw-bold small text-muted text-nowrap mb-0">Mostrar:</label>
                        <select name="limit" id="limitSelect" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="5" <?= $limit == '5' ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $limit == '10' ? 'selected' : '' ?>>10</option>
                            <option value="15" <?= $limit == '15' ? 'selected' : '' ?>>15</option>
                            <option value="25" <?= $limit == '25' ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $limit == '50' ? 'selected' : '' ?>>50</option>
                            <option value="all" <?= $limit == 'all' ? 'selected' : '' ?>>Todos</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label for="statusFilter" class="fw-bold small text-muted text-nowrap mb-0">Estatus:</label>
                        <select name="status" id="statusFilter" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Todos</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light small text-muted">
                <span>Mostrar <strong><?= $limit === 'all' ? 'Todos' : htmlspecialchars($limit) ?></strong> registros por página</span>
                <span>Total de despachadores: <strong><?= $totalRows ?></strong></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-3 text-start">Nombre</th>
                            <th class="text-center">No. Empleado</th>
                            <th class="text-start">Puesto</th>
                            <th class="text-start">Jefe Inmediato</th>
                            <th class="text-center">Estatus de Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dispatchers)): ?>
                            <?php foreach ($dispatchers as $d): ?>
                                <?php 
                                    $fullName = trim($d['first_name'] . ' ' . $d['last_name_paternal'] . ' ' . ($d['last_name_maternal'] ?? '')); 
                                ?>
                                <tr>
                                    <td class="ps-3 text-start">
                                        <a href="<?= BASE_URL ?>/scorecard/individuals?user_id=<?= $d['id'] ?>&semester=<?= urlencode($currentSemester) ?>&status=<?= urlencode($statusFilter) ?>&limit=<?= urlencode($limit) ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= htmlspecialchars($fullName) ?>
                                        </a>
                                    </td>
                                    <td class="text-center fw-semibold">
                                        <?= htmlspecialchars($d['employee_number'] ?? 'S/N') ?>
                                    </td>
                                    <td class="text-start">
                                        <?= htmlspecialchars($d['position_name'] ?? 'Despachador') ?>
                                    </td>
                                    <td class="text-start text-muted">
                                        <?= htmlspecialchars($d['supervisor_name'] ?: 'N/A') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($d['status'] === 'active'): ?>
                                            <span class="badge bg-success-subtle text-success px-3 py-1 border border-success-subtle rounded-pill">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 border border-secondary-subtle rounded-pill">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No se encontraron despachadores registrados con los filtros seleccionados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1 && $limit !== 'all'): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <span class="small text-muted">Página <?= $page ?> de <?= $totalPages ?></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/scorecard/individuals?status=<?= urlencode($statusFilter) ?>&limit=<?= urlencode($limit) ?>&page=<?= $page - 1 ?>">Anterior</a>
                        </li>
                        <li class="page-item disabled"><span class="page-link"><?= $page ?></span></li>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/scorecard/individuals?status=<?= urlencode($statusFilter) ?>&limit=<?= urlencode($limit) ?>&page=<?= $page + 1 ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- 2. VISTA BOLETA INDIVIDUAL DEL COLABORADOR -->
    <div class="card border-0 shadow-sm mb-3 bg-primary text-white">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <span class="text-uppercase small text-white-50 d-block">Boleta de Desempeño Individual</span>
                <h5 class="fw-bold mb-0" id="userNameLabel">
                    <?= htmlspecialchars(trim($selectedUser['first_name'] . ' ' . $selectedUser['last_name_paternal'] . ' ' . ($selectedUser['last_name_maternal'] ?? ''))) ?>
                </h5>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-primary fw-bold">No. Empleado: <?= htmlspecialchars($selectedUser['employee_number'] ?? 'S/N') ?></span>
            </div>
        </div>
    </div>

    <!-- CRITERIOS DE SEMÁFORO -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-light p-2">
                <span class="fw-bold text-dark mb-1 d-block small"><i class="bi bi-info-circle-fill text-primary me-2"></i> CRITERIOS DE SEMÁFORO (Porcentajes)</span>
                <div class="d-flex gap-3">
                    <span class="badge bg-danger text-white px-2 py-1 small">Rojo: 0 a 60</span>
                    <span class="badge bg-warning text-dark px-2 py-1 small">Amarillo: 60 a 80</span>
                    <span class="badge bg-success text-white px-2 py-1 small">Verde: 80 a 100</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULARIO PRINCIPAL DE LA BOLETA INDIVIDUAL -->
    <form id="individualScorecardForm" action="<?= BASE_URL ?>/scorecard/individuals" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
        <input type="hidden" name="semester" value="<?= $currentSemester ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <input type="hidden" name="limit" value="<?= htmlspecialchars($limit) ?>">

        <!-- SELECTOR DE SEMESTRE -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2 d-flex align-items-center gap-3">
                <label for="semesterSelectInd" class="fw-bold text-uppercase text-dark mb-0">Semestre:</label>
                <select id="semesterSelectInd" class="form-select w-auto fw-semibold form-select-sm">
                    <?php foreach ($semesters as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $currentSemester === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- TABLA HISTORIAL SEMESTRAL INDIVIDUAL -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white fw-bold py-2 text-uppercase text-center small">
                Historial Semestral Individual
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered text-center align-middle mb-0 text-nowrap">
                    <thead class="table-dark small">
                        <tr>
                            <th>Mes</th>
                            <th>Ventas (%)</th>
                            <th>Desempeño (%)</th>
                            <th>Incidencias</th>
                            <th>Asistencia Hábitos (%)</th>
                            <th>Cargas Equivocadas</th>
                            <th>6 Pasos (%)</th>
                            <th>Quejas del Cliente</th>
                            <th>Cobros Equivocados</th>
                            <th>Aceites</th>
                            <th>Facturas</th>
                            <th>Bono Productividad (%)</th>
                            <th>Faltas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totals = [
                            'sales' => 0, 'performance' => 0, 'incidents' => 0, 'attendance_habits' => 0,
                            'wrong_loadings' => 0, 'six_steps' => 0, 'client_complaints' => 0, 
                            'wrong_charges' => 0, 'oils' => 0, 'invoices' => 0, 
                            'productivity_bonus' => 0, 'absences' => 0, 'count' => 0
                        ];

                        $fieldsMap = [
                            'sales' => 'Ventas',
                            'performance' => 'Desempeño',
                            'incidents' => 'Incidencias',
                            'attendance_habits' => 'Asistencia Hábitos',
                            'wrong_loadings' => 'Cargas Equivocadas',
                            'six_steps' => '6 Pasos',
                            'client_complaints' => 'Quejas del Cliente',
                            'wrong_charges' => 'Cobros Equivocados',
                            'oils' => 'Aceites',
                            'invoices' => 'Facturas',
                            'productivity_bonus' => 'Bono Productividad',
                            'absences' => 'Faltas'
                        ];

                        foreach ($months as $monthName => $mIndex): 
                            $row = $savedData[$monthName] ?? [];
                            $hasData = !empty($row);
                            if ($hasData) $totals['count']++;
                        ?>
                            <tr>
                                <td class="fw-bold bg-light"><?= $monthName ?></td>
                                <?php 
                                foreach ($fieldsMap as $englishField => $spanishLabel) {
                                    $val = $row[$englishField] ?? 0;
                                    if ($hasData) $totals[$englishField] += $val;
                                    
                                    $bgClass = '';
                                    if (in_array($englishField, ['sales', 'performance', 'attendance_habits', 'six_steps', 'productivity_bonus']) && $hasData) {
                                        if ($val >= 80) {
                                            $bgClass = 'bg-success text-white fw-bold';
                                        } elseif ($val >= 60) {
                                            $bgClass = 'bg-warning text-dark fw-bold';
                                        } else {
                                            $bgClass = 'bg-danger text-white fw-bold';
                                        }
                                    }
                                    
                                    $isPercent = in_array($englishField, ['sales', 'performance', 'attendance_habits', 'six_steps', 'productivity_bonus']);
                                    $displayVal = $hasData ? ($isPercent ? round($val) . '%' : (int)$val) : '-';
                                    
                                    echo '<td class="'.$bgClass.'">'.$displayVal.'</td>';
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-primary fw-bold">
                        <!-- FILA 1: TOTALES -->
                        <tr>
                            <td>Totales</td>
                            <?php 
                            $count = max(1, $totals['count']);
                            $tSales = round($totals['sales'] / $count);
                            $tPerf = round($totals['performance'] / $count);
                            $tHabits = round($totals['attendance_habits'] / $count);
                            $tSteps = round($totals['six_steps'] / $count);
                            $tProd = round($totals['productivity_bonus'] / $count);

                            echo '<td>'.$tSales.'%</td>';
                            echo '<td>'.$tPerf.'%</td>';
                            echo '<td>'.(int)$totals['incidents'].'</td>';
                            echo '<td>'.$tHabits.'%</td>';
                            echo '<td>'.(int)$totals['wrong_loadings'].'</td>';
                            echo '<td>'.$tSteps.'%</td>';
                            echo '<td>'.(int)$totals['client_complaints'].'</td>';
                            echo '<td>'.(int)$totals['wrong_charges'].'</td>';
                            echo '<td>'.(int)$totals['oils'].'</td>';
                            echo '<td>'.(int)$totals['invoices'].'</td>';
                            echo '<td>'.$tProd.'%</td>';
                            echo '<td>'.(int)$totals['absences'].'</td>';
                            ?>
                        </tr>
                        <!-- FILA 2: FINALES (EDITABLE) -->
                        <tr class="table-warning">
                            <td class="text-dark">Finales (Editable)</td>
                            <td><input type="text" name="final_sales" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_sales']) ? round($semesterFinals['final_sales']) . '%' : $tSales . '%' ?>"></td>
                            <td><input type="text" name="final_performance" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_performance']) ? round($semesterFinals['final_performance']) . '%' : $tPerf . '%' ?>"></td>
                            <td><input type="text" name="final_incidents" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_incidents']) ? (int)$semesterFinals['final_incidents'] : (int)$totals['incidents'] ?>"></td>
                            <td><input type="text" name="final_attendance_habits" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_attendance_habits']) ? round($semesterFinals['final_attendance_habits']) . '%' : $tHabits . '%' ?>"></td>
                            <td><input type="text" name="final_wrong_loadings" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_wrong_loadings']) ? (int)$semesterFinals['final_wrong_loadings'] : (int)$totals['wrong_loadings'] ?>"></td>
                            <td><input type="text" name="final_six_steps" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_six_steps']) ? round($semesterFinals['final_six_steps']) . '%' : $tSteps . '%' ?>"></td>
                            <td><input type="text" name="final_client_complaints" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_client_complaints']) ? (int)$semesterFinals['final_client_complaints'] : (int)$totals['client_complaints'] ?>"></td>
                            <td><input type="text" name="final_wrong_charges" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_wrong_charges']) ? (int)$semesterFinals['final_wrong_charges'] : (int)$totals['wrong_charges'] ?>"></td>
                            <td><input type="text" name="final_oils" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_oils']) ? (int)$semesterFinals['final_oils'] : (int)$totals['oils'] ?>"></td>
                            <td><input type="text" name="final_invoices" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_invoices']) ? (int)$semesterFinals['final_invoices'] : (int)$totals['invoices'] ?>"></td>
                            <td><input type="text" name="final_productivity_bonus" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_productivity_bonus']) ? round($semesterFinals['final_productivity_bonus']) . '%' : $tProd . '%' ?>"></td>
                            <td><input type="text" name="final_absences" class="form-control form-control-sm text-center fw-bold bg-white" value="<?= isset($semesterFinals['final_absences']) ? (int)$semesterFinals['final_absences'] : (int)$totals['absences'] ?>"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- SECCIONES INFERIORES -->
        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="row g-0 h-100">
                        <div class="col-7 border-end d-flex flex-column">
                            <div class="bg-dark text-white fw-bold text-center py-1 small">CENTENARIOS</div>
                            <div class="card-body py-1 d-flex align-items-center justify-content-around">
                                <?php $semesterNum = str_starts_with($currentSemester, '1-') ? '1' : '2'; ?>
                                <div class="text-center">
                                    <span class="small text-muted d-block" style="font-size: 7.5px;">Recibidos</span>
                                    <span class="fw-bold text-dark"><?= (int)($summaryData['total_centenarios_received'] ?? 0) ?></span>
                                </div>
                                <div class="text-center">
                                    <span class="small text-muted d-block" style="font-size: 7.5px;">Canjeados</span>
                                    <span class="fw-bold text-dark"><?= (int)($summaryData['total_centenarios_redeemed'] ?? 0) ?></span>
                                </div>
                                <div class="text-center">
                                    <span class="small text-muted d-block" style="font-size: 7.5px;">Importe Canje</span>
                                    <span class="fw-bold text-dark">$<?= number_format($summaryData['total_redemption_amount'] ?? 0, 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-5 d-flex flex-column">
                            <div class="bg-dark text-white fw-bold text-center py-1 small">EVALUACIÓN 360º</div>
                            <div class="card-body py-1 d-flex align-items-center justify-content-around text-center">
                                <div>
                                    <span class="small text-muted d-block" style="font-size: 7.5px;">360º General</span>
                                    <span class="fw-bold text-dark"><?= round($generalSummaryData['avg_eval_360'] ?? 0) ?>%</span>
                                </div>
                                <div>
                                    <span class="small text-muted d-block" style="font-size: 7.5px;">360º Individual</span>
                                    <span class="fw-bold text-dark"><?= round($summaryData['avg_eval_360'] ?? 0) ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white fw-bold text-center py-1 small">CÁLCULOS GENERALES</div>
                    <div class="card-body py-1 d-flex align-items-center justify-content-around">
                        <div class="text-center">
                            <span class="small text-muted d-block" style="font-size: 7.5px;">Desempeño General</span>
                            <span class="fw-bold text-dark"><?= round($generalSummaryData['avg_performance'] ?? 0) ?>%</span>
                        </div>
                        <div class="text-center">
                            <span class="small text-muted d-block" style="font-size: 7.5px;">Desempeño Individual</span>
                            <span class="fw-bold text-dark"><?= round($summaryData['avg_performance'] ?? 0) ?>%</span>
                        </div>
                        <div class="text-center">
                            <span class="small text-muted d-block" style="font-size: 7.5px;">Calificación Final</span>
                            <?php 
                                $valQual = isset($semesterFinals['final_qualification']) ? round($semesterFinals['final_qualification']) : round($summaryData['avg_eval_360'] ?? 0);
                            ?>
                            <input type="text" name="final_qualification" class="form-control form-control-sm text-center fw-bold bg-light" style="width: 70px; padding: 1px;" value="<?= $valQual ?>%">
                        </div>
                        <div class="text-center">
                            <span class="small text-muted d-block" style="font-size: 7.5px;">Proyecto S<?= $semesterNum ?></span>
                            <?php 
                                $valProj = isset($semesterFinals['final_semester_project']) ? $semesterFinals['final_semester_project'] : ($summaryData['total_semester_project_1'] ?? 0);
                            ?>
                            <input type="text" name="final_semester_project" class="form-control form-control-sm text-center fw-bold bg-light" style="width: 85px; padding: 1px;" value="$<?= number_format((float)$valProj, 2) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const semesterSelectInd = document.getElementById('semesterSelectInd');
        if (semesterSelectInd) {
            semesterSelectInd.addEventListener('change', function() {
                const selectedSemester = this.value;
                window.location.href = '<?= BASE_URL ?>/scorecard/individuals?user_id=<?= $selectedUserId ?>&semester=' + selectedSemester + '&status=<?= urlencode($statusFilter) ?>&limit=<?= urlencode($limit) ?>';
            });
        }
    });

    function printScorecardPDF() {
        const userName = document.getElementById('userNameLabel') ? document.getElementById('userNameLabel').innerText.trim().replace(/\s+/g, '_') : 'Colaborador';
        const semesterSelect = document.getElementById('semesterSelectInd');
        const semesterText = semesterSelect ? semesterSelect.options[semesterSelect.selectedIndex].text.replace(/\s+/g, '_') : 'Semestre';
        
        document.title = `Boleta_Individual_${userName}_${semesterText}`;
        window.print();
    }
    </script>
<?php endif; ?>
<div class="d-none">
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="fw-bold text-brand-dark mb-0">Boleta / General</h4>
</div>

<!-- CRITERIOS DE SEMÁFORO -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm bg-light p-3">
            <span class="fw-bold text-dark mb-2 d-block"><i class="bi bi-info-circle-fill text-primary me-2"></i> CRITERIOS DE SEMÁFORO (Porcentajes)</span>
            <div class="d-flex gap-4">
                <span class="badge bg-danger text-white px-3 py-2">Rojo: 0 a 60</span>
                <span class="badge bg-warning text-dark px-3 py-2">Amarillo: 60 a 80</span>
                <span class="badge bg-success text-white px-3 py-2">Verde: 80 a 100</span>
            </div>
        </div>
    </div>
</div>

<!-- SELECTOR DE SEMESTRE -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <label for="semesterSelect" class="fw-bold text-uppercase text-dark">Semestre:</label>
        <select id="semesterSelect" class="form-select w-auto fw-semibold">
            <?php foreach ($semesters as $key => $label): ?>
                <option value="<?= $key ?>" <?= $currentSemester === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- TABLA HISTORIAL SEMESTRAL -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold py-3 text-uppercase text-center">
        Historial Semestral
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
                    <th>6 pasos (%)</th>
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
                            
                            $displayVal = $hasData ? ($englishField == 'sales' || $englishField == 'performance' || $englishField == 'attendance_habits' || $englishField == 'six_steps' || $englishField == 'productivity_bonus' ? $val . '%' : $val) : '-';
                            
                            echo '<td class="'.$bgClass.'">'.$displayVal.'</td>';
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-primary fw-bold">
                <tr>
                    <td>Totales / Promedio</td>
                    <?php 
                    $count = max(1, $totals['count']);
                    echo '<td>'.number_format($totals['sales'] / $count, 1).'%</td>';
                    echo '<td>'.number_format($totals['performance'] / $count, 1).'%</td>';
                    echo '<td>'.$totals['incidents'].'</td>';
                    echo '<td>'.number_format($totals['attendance_habits'] / $count, 1).'%</td>';
                    echo '<td>'.$totals['wrong_loadings'].'</td>';
                    echo '<td>'.number_format($totals['six_steps'] / $count, 1).'%</td>';
                    echo '<td>'.$totals['client_complaints'].'</td>';
                    echo '<td>'.$totals['wrong_charges'].'</td>';
                    echo '<td>'.$totals['oils'].'</td>';
                    echo '<td>'.$totals['invoices'].'</td>';
                    echo '<td>'.number_format($totals['productivity_bonus'] / $count, 1).'%</td>';
                    echo '<td>'.$totals['absences'].'</td>';
                    ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- SECCIONES INFERIORES: CENTENARIOS, EVALUACIÓN 360º Y CÁLCULOS GENERALES -->
<div class="row">
    <!-- BLOQUE IZQUIERDO: CENTENARIOS Y EVALUACIÓN 360º -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="row g-0 h-100">
                <!-- Centenarios -->
                <div class="col-8 border-end d-flex flex-column">
                    <div class="bg-dark text-white fw-bold text-center py-2 small">CENTENARIOS</div>
                    <div class="card-body py-3 d-flex align-items-center justify-content-around">
                        <?php 
                        $semesterNum = str_starts_with($currentSemester, '1-') ? '1' : '2';
                        ?>
                        <div class="text-center">
                            <span class="small text-muted d-block">Recibidos</span>
                            <span class="fs-6 fw-bold text-dark"><?= $summaryData['total_centenarios_received'] ?? 0 ?></span>
                        </div>
                        <div class="text-center">
                            <span class="small text-muted d-block">Canjeados</span>
                            <span class="fs-6 fw-bold text-dark"><?= $summaryData['total_centenarios_redeemed'] ?? 0 ?></span>
                        </div>
                        <div class="text-center">
                            <span class="small text-muted d-block">Importe Canje</span>
                            <span class="fs-6 fw-bold text-dark">$<?= number_format($summaryData['total_redemption_amount'] ?? 0, 2) ?></span>
                        </div>
                    </div>
                </div>
                <!-- Evaluación 360º -->
                <div class="col-4 d-flex flex-column">
                    <div class="bg-dark text-white fw-bold text-center py-2 small">EVALUACIÓN 360º</div>
                    <div class="card-body py-3 d-flex flex-column align-items-center justify-content-center text-center">
                        <span class="small text-muted d-block">360º General S<?= $semesterNum ?></span>
                        <span class="fs-6 fw-bold text-dark"><?= number_format($summaryData['avg_eval_360'] ?? 0, 2) ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BLOQUE DERECHO: CÁLCULOS GENERALES -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white fw-bold text-center py-2">CÁLCULOS GENERALES</div>
            <div class="card-body py-3 d-flex align-items-center justify-content-around">
                <div class="text-center">
                    <span class="small text-muted d-block">Desempeño General</span>
                    <span class="fs-6 fw-bold text-dark"><?= number_format($summaryData['avg_performance'] ?? 0, 1) ?>%</span>
                </div>
                <div class="text-center">
                    <span class="small text-muted d-block">Calificación Final</span>
                    <span class="fs-6 fw-bold text-dark"><?= number_format($summaryData['avg_eval_360'] ?? 0, 1) ?>%</span>
                </div>
                <div class="text-center">
                    <span class="small text-muted d-block">Proyecto Semestre <?= $semesterNum ?></span>
                    <span class="fs-6 fw-bold text-dark">$<?= number_format($summaryData['total_semester_project_1'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const semesterSelect = document.getElementById('semesterSelect');
    if (semesterSelect) {
        semesterSelect.addEventListener('change', function() {
            const selectedSemester = this.value;
            window.location.href = '<?= BASE_URL ?>/scorecard/general?semester=' + selectedSemester;
        });
    }
});
</script>
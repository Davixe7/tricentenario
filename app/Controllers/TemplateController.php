<?php

require_once __DIR__ . '/../Models/Template.php';

class TemplateController {
    private $templateModel;

    private $types = [
        'reporte-semanal' => [
            'key' => 'reporte_semanal',
            'title' => '1. Reporte Mensual de Despachadores',
            'tab_title' => 'Reporte Mensual',
            'filename' => '1_Reporte_Mensual_Despachadores.xlsx',
            'has_semester' => false,
            'date_format_note' => false
        ],
        'bitacora-incidencias' => [
            'key' => 'bitacora_incidencias',
            'title' => '2. Bitácora de Incidencias',
            'tab_title' => 'Bitácora de Incidencias',
            'filename' => '2_Bitacora_de_Incidencias.xlsx',
            'has_semester' => false,
            'date_format_note' => true
        ],
        'calificaciones-360' => [
            'key' => 'calificaciones_360',
            'title' => '3. Calificaciones 360º',
            'tab_title' => 'Calificaciones 360º',
            'filename' => '3_Calificaciones_360.xlsx',
            'has_semester' => true,
            'date_format_note' => false
        ],
        'proyecto-semestre' => [
            'key' => 'proyecto_semestre',
            'title' => '4. Proyecto Semestre',
            'tab_title' => 'Proyecto Semestre',
            'filename' => '4_Proyecto_Semestre.xlsx',
            'has_semester' => true,
            'date_format_note' => false
        ]
    ];

    public function __construct() {
        Auth::check();
        $this->templateModel = new Template();
    }

    public function reporteSemanal() { $this->renderView('reporte-semanal'); }
    public function bitacoraIncidencias() { $this->renderView('bitacora-incidencias'); }
    public function calificaciones360() { $this->renderView('calificaciones-360'); }
    public function proyectoSemestre() { $this->renderView('proyecto-semestre'); }

    private function renderView($slug) {
        $config = $this->types[$slug] ?? null;
        if (!$config) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $semester = $_GET['semester'] ?? null;
        $lastLog = $this->templateModel->getLastLog($config['key'], $semester);

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/templates/show.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function download($slug) {
        $config = $this->types[$slug] ?? null;
        if (!$config) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $filePath = __DIR__ . '/../../public/assets/templates/' . $config['filename'];
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            die('El archivo de la plantilla no se encuentra disponible.');
        }
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $slug = $_POST['template_slug'] ?? '';
            $config = $this->types[$slug] ?? null;

            if (!$config) {
                header('Location: ' . BASE_URL);
                exit;
            }

            $semesterNum = $config['has_semester'] ? ($_POST['semester'] ?? null) : null;
            if ($config['has_semester'] && empty($semesterNum)) {
                header('Location: ' . BASE_URL . '/templates/' . $slug . '?error=semester_required');
                exit;
            }

            if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'xlsx' && $ext !== 'xls') {
                    header('Location: ' . BASE_URL . '/templates/' . $slug . '?error=invalid_file');
                    exit;
                }

                $uploadDir = UPLOAD_DIR . 'templates/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $originalName = $_FILES['excel_file']['name'];
                $filename = $config['key'] . ($semesterNum ? '_sem' . $semesterNum : '') . '_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $targetFile)) {
                    $processingResult = $this->processExcelDataToScorecard($targetFile, $config['key'], $semesterNum, $originalName);
                    $rowCount = $processingResult['row_count'];
                    
                    $userId = $_SESSION['user']['id'] ?? null;
                    $relativePath = 'uploads/templates/' . $filename;

                    $this->templateModel->saveLog($config['key'], $rowCount, $relativePath, $userId, $semesterNum);

                    header('Location: ' . BASE_URL . '/templates/' . $slug . '?success=1&count=' . $rowCount . ($semesterNum ? '&semester=' . $semesterNum : ''));
                    exit;
                }
            }
        }
        header('Location: ' . BASE_URL);
        exit;
    }

    private function processExcelDataToScorecard($filePath, $templateKey, $semesterNum, $originalName = '') {
        if (!class_exists('ZipArchive')) {
            return ['row_count' => 0];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            return ['row_count' => 0];
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        if (!$sheetXml) {
            return ['row_count' => 0];
        }

        $sharedStrings = [];
        if ($sharedStringsXml) {
            $ssXml = simplexml_load_string($sharedStringsXml);
            if ($ssXml && isset($ssXml->si)) {
                foreach ($ssXml->si as $si) {
                    $sharedStrings[] = (string)$si->t;
                }
            }
        }

        $xml = simplexml_load_string($sheetXml);
        if (!isset($xml->sheetData->row)) {
            return ['row_count' => 0];
        }

        $rows = $xml->sheetData->row;
        if (count($rows) <= 1) {
            return ['row_count' => 0];
        }

        $headerMap = [];
        $firstRow = $rows[0];
        foreach ($firstRow->c as $c) {
            $coord = (string)$c['r'];
            preg_match('/^[A-Z]+/', $coord, $m);
            $colLetter = $m[0] ?? '';
            
            $cellType = (string)$c['t'];
            $cellVal = isset($c->v) ? (string)$c->v : '';
            if ($cellType === 's' && isset($sharedStrings[(int)$cellVal])) {
                $cellVal = $sharedStrings[(int)$cellVal];
            }
            $headerMap[$colLetter] = trim(strtoupper($cellVal));
        }

        $validRowCount = 0;
        $monthlyGlobalAccumulator = [];
        $individualRecords = [];

        $currentYear = '2026';
        if (preg_match('/(20\d{2})/', $originalName, $match)) {
            $currentYear = $match[1];
        } elseif (preg_match('/(20\d{2})/', $filePath, $match)) {
            $currentYear = $match[1];
        }

        $monthNameToIndex = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6,
            'JULIO' => 7, 'AGOSTO' => 8, 'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];

        // Mapear los números de empleado existentes en la base de datos
        $db = Database::getInstance();
        $userMap = [];
        $stmtUsers = $db->query("SELECT id, employee_number FROM users WHERE employee_number IS NOT NULL");
        while ($uRow = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
            $userMap[(int)$uRow['employee_number']] = (int)$uRow['id'];
        }

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowData = [];
            foreach ($row->c as $c) {
                $coord = (string)$c['r'];
                preg_match('/^[A-Z]+/', $coord, $m);
                $colLetter = $m[0] ?? '';
                
                $headerName = $headerMap[$colLetter] ?? '';
                if ($headerName === '') continue;

                $cellType = (string)$c['t'];
                $cellVal = isset($c->v) ? (string)$c->v : '';
                if ($cellType === 's' && isset($sharedStrings[(int)$cellVal])) {
                    $cellVal = $sharedStrings[(int)$cellVal];
                }
                $rowData[$headerName] = $cellVal;
            }

            if (empty(array_filter($rowData))) {
                continue;
            }

            $validRowCount++;

            if ($templateKey === 'reporte_semanal') {
                $rawMonthName = strtoupper(trim($rowData['MES'] ?? 'ENERO'));
                $monthIndex = $monthNameToIndex[$rawMonthName] ?? (int)($rowData['# MES'] ?? 1);
                $monthName = ucwords(strtolower($rawMonthName));
                if (empty($monthName)) { $monthName = 'Enero'; $monthIndex = 1; }

                $semesterKey = ($monthIndex <= 6 ? '1' : '2') . '-' . $currentYear;
                $empNum = intval($rowData['# EMPLEADO'] ?? 0);
                $matchedUserId = $userMap[$empNum] ?? null;

                // 1. Acumulador Global (para la boleta general)
                if (!isset($monthlyGlobalAccumulator[$semesterKey][$monthName])) {
                    $monthlyGlobalAccumulator[$semesterKey][$monthName] = [
                        'month_index' => $monthIndex,
                        'sales_sum' => 0, 'sales_count' => 0,
                        'steps_sum' => 0, 'steps_count' => 0,
                        'habits_sum' => 0, 'habits_count' => 0,
                        'bonus_sum' => 0, 'bonus_count' => 0,
                        'oils' => 0, 'wrong_loadings' => 0, 'client_complaints' => 0,
                        'wrong_charges' => 0, 'invoices' => 0, 'absences' => 0,
                        'centenarios_received' => 0, 'centenarios_redeemed' => 0, 'redemption_amount' => 0
                    ];
                }

                $mAcc = &$monthlyGlobalAccumulator[$semesterKey][$monthName];
                $litros = floatval($rowData['LITROS'] ?? 0);
                $meta = floatval($rowData['META'] ?? 2500);
                $ventasPct = $meta > 0 ? (($litros / $meta) * 100) : 0;

                $mAcc['sales_sum'] += $ventasPct;
                $mAcc['sales_count']++;
                $mAcc['oils'] += intval($rowData['ACEITES'] ?? 0);

                $stepVal = floatval($rowData['6 PASOS'] ?? 0);
                if ($stepVal > 0 && $stepVal <= 1) $stepVal *= 100;
                $mAcc['steps_sum'] += $stepVal;
                $mAcc['steps_count']++;

                $habitVal = floatval($rowData['ASISTENCIA HÁBITOS'] ?? 0);
                if ($habitVal > 0 && $habitVal <= 1) $habitVal *= 100;
                $mAcc['habits_sum'] += $habitVal;
                $mAcc['habits_count']++;

                $mAcc['wrong_loadings'] += intval($rowData['CARGAS EQUIVOCADAS'] ?? 0);
                $mAcc['client_complaints'] += intval($rowData['QUEJAS DEL CLIENTE'] ?? 0);
                $mAcc['wrong_charges'] += intval($rowData['COBROS EQUIVOCADOS'] ?? 0);
                $mAcc['invoices'] += intval($rowData['FACTURAS'] ?? 0);

                $bonusVal = floatval($rowData['BONO PRODUCTIVIDAD'] ?? 0);
                if ($bonusVal > 0 && $bonusVal <= 1) $bonusVal *= 100;
                $mAcc['bonus_sum'] += $bonusVal;
                $mAcc['bonus_count']++;

                $mAcc['absences'] += intval($rowData['FALTAS'] ?? 0);
                $mAcc['centenarios_received'] += intval($rowData['CENTENARIOS RECIBIDOS'] ?? 0);
                $mAcc['centenarios_redeemed'] += intval($rowData['CENTENARIOS OTORGADOS'] ?? 0);
                $mAcc['redemption_amount'] += floatval($rowData['IMPORTE CANJE'] ?? 0);

                // 2. Registro Individual (si el empleado existe en la BD)
                if ($matchedUserId) {
                    $indSales = $ventasPct;
                    if ($indSales > 999.99) $indSales = 999.99;

                    $indStep = floatval($rowData['6 PASOS'] ?? 0);
                    if ($indStep > 0 && $indStep <= 1) $indStep *= 100;

                    $indHabit = floatval($rowData['ASISTENCIA HÁBITOS'] ?? 0);
                    if ($indHabit > 0 && $indHabit <= 1) $indHabit *= 100;

                    $indBonus = floatval($rowData['BONO PRODUCTIVIDAD'] ?? 0);
                    if ($indBonus > 0 && $indBonus <= 1) $indBonus *= 100;

                    $individualRecords[] = [
                        'semester' => $semesterKey,
                        'month_name' => $monthName,
                        'month_index' => $monthIndex,
                        'user_id' => $matchedUserId,
                        'sales' => $indSales,
                        'performance' => $indSales,
                        'attendance_habits' => $indHabit,
                        'six_steps' => $indStep,
                        'productivity_bonus' => $indBonus,
                        'oils' => intval($rowData['ACEITES'] ?? 0),
                        'wrong_loadings' => intval($rowData['CARGAS EQUIVOCADAS'] ?? 0),
                        'client_complaints' => intval($rowData['QUEJAS DEL CLIENTE'] ?? 0),
                        'wrong_charges' => intval($rowData['COBROS EQUIVOCADOS'] ?? 0),
                        'invoices' => intval($rowData['FACTURAS'] ?? 0),
                        'absences' => intval($rowData['FALTAS'] ?? 0),
                        'centenarios_received' => intval($rowData['CENTENARIOS RECIBIDOS'] ?? 0),
                        'centenarios_redeemed' => intval($rowData['CENTENARIOS OTORGADOS'] ?? 0),
                        'redemption_amount' => floatval($rowData['IMPORTE CANJE'] ?? 0)
                    ];
                }
            }
        }

        // Guardar Totales Globales en monthly_scorecards
        foreach ($monthlyGlobalAccumulator as $semKey => $monthsArr) {
            foreach ($monthsArr as $mName => $vals) {
                $mIdx = $vals['month_index'] ?? 1;
                $sCount = max(1, $vals['sales_count']);
                $stCount = max(1, $vals['steps_count']);
                $hCount = max(1, $vals['habits_count']);
                $bCount = max(1, $vals['bonus_count']);

                $salesVal = round($vals['sales_sum'] / $sCount, 2);
                if ($salesVal > 999.99) $salesVal = 999.99;

                $dataToSave = [
                    'sales' => $salesVal,
                    'performance' => $salesVal,
                    'attendance_habits' => round($vals['habits_sum'] / $hCount, 2),
                    'six_steps' => round($vals['steps_sum'] / $stCount, 2),
                    'productivity_bonus' => round($vals['bonus_sum'] / $bCount, 2),
                    'oils' => $vals['oils'],
                    'wrong_loadings' => $vals['wrong_loadings'],
                    'client_complaints' => $vals['client_complaints'],
                    'wrong_charges' => $vals['wrong_charges'],
                    'invoices' => $vals['invoices'],
                    'absences' => $vals['absences'],
                    'centenarios_received' => $vals['centenarios_received'],
                    'centenarios_redeemed' => $vals['centenarios_redeemed'],
                    'redemption_amount' => $vals['redemption_amount']
                ];

                $this->templateModel->updateMonthlyScorecardFromData($semKey, $mName, $mIdx, $dataToSave);
            }
        }

        // Guardar Registros Individuales en user_monthly_scorecards
        foreach ($individualRecords as $ind) {
            $uId = $ind['user_id'];
            $semKey = $ind['semester'];
            $mName = $ind['month_name'];
            $mIdx = $ind['month_index'];
            unset($ind['user_id'], $ind['semester'], $ind['month_name'], $ind['month_index']);

            $this->templateModel->updateUserMonthlyScorecard($semKey, $mName, $mIdx, $uId, $ind);
        }

        return ['row_count' => max(1, $validRowCount)];
    }
}
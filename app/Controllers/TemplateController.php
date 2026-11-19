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

    private function logUnmatchedRow($db, $templateKey, $semesterKey, $monthName, $empNum, $empName, $excelRow, $rowData, $reason) {
        $sql = "INSERT INTO unmatched_template_rows
                (template_type, semester, month_name, employee_number, employee_name, row_number, raw_payload, reason)
                VALUES (:template_type, :semester, :month_name, :employee_number, :employee_name, :row_number, :raw_payload, :reason)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':template_type' => $templateKey,
            ':semester' => $semesterKey,
            ':month_name' => $monthName,
            ':employee_number' => $empNum > 0 ? $empNum : null,
            ':employee_name' => $empName !== '' ? $empName : null,
            ':row_number' => $excelRow,
            ':raw_payload' => json_encode($rowData, JSON_UNESCAPED_UNICODE),
            ':reason' => $reason
        ]);
    }

    private function incidentsCountToPercent($numIncidencias) {
        $n = (int)$numIncidencias;
        if ($n === 0) return 100.0;
        if ($n === 1) return 90.0;
        if ($n === 2) return 80.0;
        if ($n === 3) return 70.0;
        if ($n === 4) return 60.0;
        return 0.0;
    }

    /**
     * Incrementa el contador de incidencias del mes para un usuario
     * (puede ser un usuario real o un ID negativo sintético para filas no emparejadas).
     */
    private function incrementMonthlyIncident($db, $userId, $semesterKey, $monthName, $monthIndex) {
        $sqlCheckCard = "SELECT id, incidents FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
        $stmtCheck = $db->prepare($sqlCheckCard);
        $stmtCheck->execute([':user_id' => $userId, ':semester' => $semesterKey, ':month_name' => $monthName]);
        $existsCard = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existsCard) {
            $sqlInsCard = "INSERT INTO user_monthly_scorecards (user_id, semester, month_name, month_index, incidents) VALUES (:user_id, :semester, :month_name, :month_index, 1)";
            $stmtInsCard = $db->prepare($sqlInsCard);
            $stmtInsCard->execute([
                ':user_id' => $userId,
                ':semester' => $semesterKey,
                ':month_name' => $monthName,
                ':month_index' => $monthIndex
            ]);
        } else {
            $sqlUpdInc = "UPDATE user_monthly_scorecards SET incidents = incidents + 1 WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
            $stmtUpdInc = $db->prepare($sqlUpdInc);
            $stmtUpdInc->execute([':user_id' => $userId, ':semester' => $semesterKey, ':month_name' => $monthName]);
        }
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
                    $text = (string)$si->t;
                    if ($text === '' && isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
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
        $individualRecords = [];

        $currentYear = '2025';
        if (preg_match('/(20\d{2})/', $originalName, $match)) {
            $currentYear = $match[1];
        } elseif (preg_match('/(20\d{2})/', $filePath, $match)) {
            $currentYear = $match[1];
        }

        $monthNameToIndex = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6,
            'JULIO' => 7, 'AGOSTO' => 8, 'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];

        $db = Database::getInstance();

        $userMap = [];
        $userNameMap = [];
        $stmtUsers = $db->query("SELECT id, employee_number, first_name, last_name_paternal, last_name_maternal FROM users");
        while ($uRow = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($uRow['employee_number'])) {
                $userMap[(int)$uRow['employee_number']] = (int)$uRow['id'];
            }
            $fullNameKey = strtolower(preg_replace('/\s+/', ' ', trim($uRow['first_name'] . ' ' . $uRow['last_name_paternal'] . ' ' . ($uRow['last_name_maternal'] ?? ''))));
            if ($fullNameKey !== '') {
                $userNameMap[$fullNameKey] = (int)$uRow['id'];
            }
            $shortNameKey = strtolower(preg_replace('/\s+/', ' ', trim($uRow['first_name'] . ' ' . $uRow['last_name_paternal'])));
            if ($shortNameKey !== '') {
                $userNameMap[$shortNameKey] = (int)$uRow['id'];
            }
        }

        $findExistingUser = function($empNum, $rawEmployeeName) use ($userMap, $userNameMap) {
            $empNumInt = (int)$empNum;
            if ($empNumInt > 0 && isset($userMap[$empNumInt])) {
                return (int)$userMap[$empNumInt];
            }
            if (!empty($rawEmployeeName)) {
                $normExcelName = strtolower(preg_replace('/\s+/', ' ', trim($rawEmployeeName)));
                if (isset($userNameMap[$normExcelName])) {
                    return (int)$userNameMap[$normExcelName];
                }
            }
            return null;
        };

        // Genera un ID negativo estable por colaborador/fila no emparejada para acumular en user_monthly_scorecards
        // sin crear registros en la tabla users ni hacer INNER JOIN con users.
        $syntheticUserId = function($empNum, $rawEmployeeName, $excelRowNum) {
            $key = ((int)$empNum > 0)
                ? 'emp:' . (int)$empNum
                : ('name:' . strtolower(preg_replace('/\s+/', ' ', trim($rawEmployeeName))) . ':row:' . (int)$excelRowNum);
            $hash = abs(crc32($key));
            return -1 * max(1, ($hash % 2000000000));
        };

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $excelRowNum = (int)($row['r'] ?? ($i + 1));
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

            if (empty(array_filter($rowData, fn($v) => $v !== null && trim((string)$v) !== ''))) {
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
                $rawEmployeeName = trim($rowData['NOMBRE'] ?? '');

                $matchedUserId = $findExistingUser($empNum, $rawEmployeeName);
                $isUnmatched = ($matchedUserId === null);
                $targetUserId = $matchedUserId ?? $syntheticUserId($empNum, $rawEmployeeName, $excelRowNum);

                if ($isUnmatched) {
                    $this->logUnmatchedRow($db, $templateKey, $semesterKey, $monthName, $empNum, $rawEmployeeName, $excelRowNum, $rowData, 'Usuario no encontrado en users (incluido en acumulado mensual)');
                }

                $litros = floatval($rowData['LITROS'] ?? 0);
                $dias = intval($rowData['DIAS'] ?? $rowData['DÍAS'] ?? 0);
                $meta = floatval($rowData['META'] ?? 2500);

                $ventasPct = ($dias > 0 && $meta > 0) ? ((($litros / $dias) / $meta) * 100.0) : 0.0;
                if ($ventasPct > 999.99) $ventasPct = 999.99;

                $aceitesCount = intval($rowData['ACEITES'] ?? 0);
                $aceitesPct = ($dias > 0) ? min(100.0, (($aceitesCount / ($dias * 2.0)) * 100.0)) : 0.0;

                $numIncidencias = 0;
                $stmtInc = $db->prepare("SELECT incidents FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name LIMIT 1");
                $stmtInc->execute([':user_id' => $targetUserId, ':semester' => $semesterKey, ':month_name' => $monthName]);
                $incRow = $stmtInc->fetch(PDO::FETCH_ASSOC);
                if ($incRow) {
                    $numIncidencias = intval($incRow['incidents']);
                }

                $incidenciasPct = $this->incidentsCountToPercent($numIncidencias);

                $stepVal = floatval($rowData['6 PASOS'] ?? 0);
                if ($stepVal <= 1.0 && $stepVal > 0) $stepVal = $stepVal * 100.0;

                $perfVal = ($ventasPct * 0.70) + ($aceitesPct * 0.15) + ($incidenciasPct * 0.10) + ($stepVal * 0.05);

                $habitVal = floatval($rowData['ASISTENCIA HÁBITOS'] ?? 0);
                if ($habitVal <= 1.0 && $habitVal > 0) $habitVal = $habitVal * 100.0;

                $bonusVal = floatval($rowData['BONO PRODUCTIVIDAD'] ?? 0);
                if ($bonusVal <= 1.0 && $bonusVal > 0) $bonusVal = $bonusVal * 100.0;

                $individualRecords[] = [
                    'semester' => $semesterKey,
                    'month_name' => $monthName,
                    'month_index' => $monthIndex,
                    'user_id' => $targetUserId,
                    'sales' => $ventasPct,
                    'performance' => $perfVal,
                    'attendance_habits' => $habitVal,
                    'six_steps' => $stepVal,
                    'productivity_bonus' => $bonusVal,
                    'oils' => $aceitesCount,
                    'incidents' => $numIncidencias,
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
            elseif ($templateKey === 'bitacora_incidencias') {
                $rawMonthName = strtoupper(trim($rowData['MES'] ?? $rowData['MES INCIDENCIA'] ?? 'ENERO'));
                $monthIndex = $monthNameToIndex[$rawMonthName] ?? 1;
                $monthName = ucwords(strtolower($rawMonthName));
                $semesterKey = ($monthIndex <= 6 ? '1' : '2') . '-' . $currentYear;

                $empReported = intval($rowData['# EMPLEADO REPORTADO'] ?? $rowData['# EMPLEADO'] ?? 0);
                $empNameReported = trim($rowData['NOMBRE DEL REPORTADO'] ?? $rowData['NOMBRE'] ?? '');

                $resolvedId = $findExistingUser($empReported, $empNameReported);
                if ($resolvedId === null) {
                    $this->logUnmatchedRow($db, $templateKey, $semesterKey, $monthName, $empReported, $empNameReported, $excelRowNum, $rowData, 'Reportado no encontrado en users (incluido en acumulado mensual)');
                    $resolvedId = $syntheticUserId($empReported, $empNameReported, $excelRowNum);
                }

                $this->incrementMonthlyIncident($db, $resolvedId, $semesterKey, $monthName, $monthIndex);
            }
            elseif ($templateKey === 'calificaciones_360') {
                $empNum = intval($rowData['# EMPLEADO'] ?? 0);
                $rawEmployeeName = trim($rowData['NOMBRE'] ?? $rowData['EMPLEADO'] ?? '');
                $score360 = floatval($rowData['CALIFICACIÓN 360º'] ?? $rowData['CALIFICACION 360'] ?? $rowData['CALIFICACION'] ?? $rowData['EVAL_360'] ?? 0);
                if ($score360 <= 1.0 && $score360 > 0) $score360 = $score360 * 100.0;

                $matchedUserId = $findExistingUser($empNum, $rawEmployeeName);
                $semKey = $semesterNum . '-' . $currentYear;

                if ($matchedUserId === null) {
                    $this->logUnmatchedRow($db, $templateKey, $semKey, null, $empNum, $rawEmployeeName, $excelRowNum, $rowData, 'Colaborador 360 no encontrado en users (incluido en acumulado semestral)');
                    $matchedUserId = $syntheticUserId($empNum, $rawEmployeeName, $excelRowNum);
                }

                if ($score360 > 0) {
                    $semMonths = ($semesterNum === '2') ? ['Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] : ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];
                    foreach ($semMonths as $mName) {
                        $mIdx = $monthNameToIndex[strtoupper($mName)] ?? 1;
                        $sqlCheckCard = "SELECT id FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
                        $stmtCheck = $db->prepare($sqlCheckCard);
                        $stmtCheck->execute([':user_id' => $matchedUserId, ':semester' => $semKey, ':month_name' => $mName]);
                        $existsCard = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                        if (!$existsCard) {
                            $sqlInsCard = "INSERT INTO user_monthly_scorecards (user_id, semester, month_name, month_index, eval_360) VALUES (:user_id, :semester, :month_name, :month_index, :eval_360)";
                            $stmtInsCard = $db->prepare($sqlInsCard);
                            $stmtInsCard->execute([
                                ':user_id' => $matchedUserId,
                                ':semester' => $semKey,
                                ':month_name' => $mName,
                                ':month_index' => $mIdx,
                                ':eval_360' => $score360
                            ]);
                        } else {
                            $sqlUpd360 = "UPDATE user_monthly_scorecards SET eval_360 = :eval_360 WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
                            $stmtU360 = $db->prepare($sqlUpd360);
                            $stmtU360->execute([
                                ':eval_360' => $score360,
                                ':user_id' => $matchedUserId,
                                ':semester' => $semKey,
                                ':month_name' => $mName
                            ]);
                        }
                    }
                }
            }
            elseif ($templateKey === 'proyecto_semestre') {
                $empNum = intval($rowData['# EMPLEADO'] ?? 0);
                $rawEmployeeName = trim($rowData['NOMBRE'] ?? $rowData['EMPLEADO'] ?? '');
                $projVal = floatval($rowData['PROYECTO SEMESTRE 1'] ?? $rowData['PROYECTO SEMESTRE 2'] ?? $rowData['PROYECTO SEMESTRE'] ?? $rowData['PROYECTO'] ?? $rowData['IMPORTE'] ?? 0);

                $matchedUserId = $findExistingUser($empNum, $rawEmployeeName);
                $semKey = $semesterNum . '-' . $currentYear;

                if ($matchedUserId === null) {
                    $this->logUnmatchedRow($db, $templateKey, $semKey, null, $empNum, $rawEmployeeName, $excelRowNum, $rowData, 'Colaborador proyecto no encontrado en users (incluido en acumulado semestral)');
                    $matchedUserId = $syntheticUserId($empNum, $rawEmployeeName, $excelRowNum);
                }

                $sqlProjUser = ($semesterNum === '2')
                    ? "UPDATE user_monthly_scorecards SET semester_project_2 = :proj WHERE user_id = :user_id AND semester = :semester"
                    : "UPDATE user_monthly_scorecards SET semester_project_1 = :proj WHERE user_id = :user_id AND semester = :semester";
                $stmtPU = $db->prepare($sqlProjUser);
                $stmtPU->execute([':proj' => $projVal, ':user_id' => $matchedUserId, ':semester' => $semKey]);
            }
        }

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
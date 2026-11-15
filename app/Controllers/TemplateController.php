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
        $processedSemesters = [];
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

        $db = Database::getInstance();
        
        $stmtPos = $db->query("SELECT id FROM positions WHERE name LIKE '%Despachador%' LIMIT 1");
        $posRow = $stmtPos->fetch(PDO::FETCH_ASSOC);
        $despachadorPositionId = $posRow ? (int)$posRow['id'] : 1;

        $userMap = [];
        $userNameMap = [];
        $stmtUsers = $db->query("SELECT id, employee_number, first_name, last_name_paternal, last_name_maternal FROM users");
        while ($uRow = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($uRow['employee_number'])) {
                $userMap[(int)$uRow['employee_number']] = (int)$uRow['id'];
            }
            $fullNameKey = strtolower(trim($uRow['first_name'] . ' ' . $uRow['last_name_paternal'] . ' ' . ($uRow['last_name_maternal'] ?? '')));
            $userNameMap[$fullNameKey] = (int)$uRow['id'];
            $shortNameKey = strtolower(trim($uRow['first_name'] . ' ' . $uRow['last_name_paternal']));
            $userNameMap[$shortNameKey] = (int)$uRow['id'];
        }

        $resolveOrCreateUser = function($empNum, $rawEmployeeName) use ($db, $despachadorPositionId, &$userMap, &$userNameMap) {
            $matchedUserId = $userMap[$empNum] ?? null;

            if (!$matchedUserId && !empty($rawEmployeeName)) {
                $normExcelName = strtolower(preg_replace('/\s+/', ' ', trim($rawEmployeeName)));
                if (isset($userNameMap[$normExcelName])) {
                    $matchedUserId = $userNameMap[$normExcelName];
                } else {
                    foreach ($userNameMap as $dbName => $dbId) {
                        if ($dbName === $normExcelName || str_contains($normExcelName, $dbName) || str_contains($dbName, $normExcelName)) {
                            $matchedUserId = $dbId;
                            break;
                        }
                    }
                }
            }

            if (!$matchedUserId && !empty($rawEmployeeName)) {
                $cleanFullName = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($rawEmployeeName))));
                $nameParts = explode(' ', $cleanFullName);
                
                $firstName = $nameParts[0] ?? 'Despachador';
                $lastNamePaternal = $nameParts[1] ?? 'Nuevo';
                $lastNameMaternal = isset($nameParts[2]) ? implode(' ', array_slice($nameParts, 2)) : '';
                
                $generatedUsername = strtolower($firstName . ($empNum > 0 ? $empNum : rand(100, 999)));

                $insertUserSql = "INSERT INTO users (employee_number, first_name, last_name_paternal, last_name_maternal, username, password, role, area_id, position_id, status) 
                                  VALUES (:emp_num, :fname, :lname_pat, :lname_mat, :uname, :pwd, 'Collaborator', 1, :pos_id, 'active')";
                $stmtInsUser = $db->prepare($insertUserSql);
                $stmtInsUser->execute([
                    ':emp_num' => $empNum > 0 ? $empNum : null,
                    ':fname' => $firstName,
                    ':lname_pat' => $lastNamePaternal,
                    ':lname_mat' => $lastNameMaternal,
                    ':uname' => $generatedUsername,
                    ':pwd' => password_hash('password123', PASSWORD_BCRYPT),
                    ':pos_id' => $despachadorPositionId
                ]);
                
                $matchedUserId = (int)$db->lastInsertId();
                if ($empNum > 0) {
                    $userMap[$empNum] = $matchedUserId;
                }
                $userNameMap[strtolower(trim($cleanFullName))] = $matchedUserId;
            }

            return $matchedUserId;
        };

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
                $processedSemesters[$semesterKey] = true;

                $empNum = intval($rowData['# EMPLEADO'] ?? 0);
                $rawEmployeeName = trim($rowData['NOMBRE'] ?? '');

                $matchedUserId = $resolveOrCreateUser($empNum, $rawEmployeeName);

                $litros = floatval($rowData['LITROS'] ?? 0);
                $dias = max(1, intval($rowData['DIAS'] ?? 1));
                $meta = floatval($rowData['META'] ?? 2500);
                
                $promedioDiario = $litros / $dias;
                $ventasPct = $meta > 0 ? (($promedioDiario / $meta) * 100) : 0;
                if ($ventasPct > 999.99) $ventasPct = 999.99;

                $aceitesCount = intval($rowData['ACEITES'] ?? 0);
                $aceitesPct = ($dias > 0) ? (($aceitesCount / ($dias * 2)) * 100) : 0;
                if ($aceitesPct > 100.0) $aceitesPct = 100.0;

                // CONSULTAR INCIDENCIAS DESDE LA BITÁCORA EN BD
                $numIncidencias = 0;
                if ($matchedUserId) {
                    $stmtInc = $db->prepare("SELECT incidents FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name LIMIT 1");
                    $stmtInc->execute([':user_id' => $matchedUserId, ':semester' => $semesterKey, ':month_name' => $monthName]);
                    $incRow = $stmtInc->fetch(PDO::FETCH_ASSOC);
                    if ($incRow) {
                        $numIncidencias = intval($incRow['incidents']);
                    }
                }

                // Ajuste de incidencias: 0 incidencias equivale a 0% en la ponderación matemática
                if ($numIncidencias === 0) $incidenciasPct = 0.0;
                elseif ($numIncidencias === 1) $incidenciasPct = 90.0;
                elseif ($numIncidencias === 2) $incidenciasPct = 80.0;
                elseif ($numIncidencias === 3) $incidenciasPct = 70.0;
                elseif ($numIncidencias === 4) $incidenciasPct = 60.0;
                else $incidenciasPct = 0.0;

                $stepVal = floatval($rowData['6 PASOS'] ?? 0);
                if ($stepVal <= 1.0 && $stepVal > 0) $stepVal = $stepVal * 100;

                $perfVal = ($ventasPct * 0.70) + ($aceitesPct * 0.15) + ($incidenciasPct * 0.10) + ($stepVal * 0.05);

                $habitVal = floatval($rowData['ASISTENCIA HÁBITOS'] ?? 0);
                if ($habitVal <= 1.0 && $habitVal > 0) $habitVal = $habitVal * 100;

                $bonusVal = floatval($rowData['BONO PRODUCTIVIDAD'] ?? 0);
                if ($bonusVal <= 1.0 && $bonusVal > 0) $bonusVal = $bonusVal * 100;

                if ($matchedUserId) {
                    $individualRecords[] = [
                        'semester' => $semesterKey,
                        'month_name' => $monthName,
                        'month_index' => $monthIndex,
                        'user_id' => $matchedUserId,
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
            }
            elseif ($templateKey === 'bitacora_incidencias') {
                $rawMonthName = strtoupper(trim($rowData['MES'] ?? $rowData['MES INCIDENCIA'] ?? 'ENERO'));
                $monthIndex = $monthNameToIndex[$rawMonthName] ?? 1;
                $monthName = ucwords(strtolower($rawMonthName));
                $semesterKey = ($monthIndex <= 6 ? '1' : '2') . '-' . $currentYear;
                $processedSemesters[$semesterKey] = true;
                
                $empReported = intval($rowData['# EMPLEADO REPORTADO'] ?? $rowData['# EMPLEADO'] ?? 0);
                $empNameReported = trim($rowData['NOMBRE DEL REPORTADO'] ?? $rowData['NOMBRE'] ?? '');
                
                $resolvedId = $resolveOrCreateUser($empReported, $empNameReported);
                if ($resolvedId) {
                    $sqlCheckCard = "SELECT id, incidents FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
                    $stmtCheck = $db->prepare($sqlCheckCard);
                    $stmtCheck->execute([':user_id' => $resolvedId, ':semester' => $semesterKey, ':month_name' => $monthName]);
                    $existsCard = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if (!$existsCard) {
                        $sqlInsCard = "INSERT INTO user_monthly_scorecards (user_id, semester, month_name, month_index, incidents) VALUES (:user_id, :semester, :month_name, :month_index, 1)";
                        $stmtInsCard = $db->prepare($sqlInsCard);
                        $stmtInsCard->execute([
                            ':user_id' => $resolvedId, 
                            ':semester' => $semesterKey, 
                            ':month_name' => $monthName, 
                            ':month_index' => $monthIndex
                        ]);
                    } else {
                        $sqlUpdInc = "UPDATE user_monthly_scorecards SET incidents = incidents + 1 WHERE user_id = :user_id AND semester = :semester AND month_name = :month_name";
                        $stmtUpdInc = $db->prepare($sqlUpdInc);
                        $stmtUpdInc->execute([':user_id' => $resolvedId, ':semester' => $semesterKey, ':month_name' => $monthName]);
                    }
                }
            }
            elseif ($templateKey === 'calificaciones_360') {
                $empNum = intval($rowData['# EMPLEADO'] ?? 0);
                $rawEmployeeName = trim($rowData['NOMBRE'] ?? $rowData['EMPLEADO'] ?? '');
                $score360 = floatval($rowData['CALIFICACIÓN 360º'] ?? $rowData['CALIFICACION 360'] ?? $rowData['CALIFICACION'] ?? $rowData['EVAL_360'] ?? 0);
                if ($score360 <= 1.0 && $score360 > 0) $score360 = $score360 * 100;
                
                $matchedUserId = $resolveOrCreateUser($empNum, $rawEmployeeName);
                $semKey = $semesterNum . '-' . $currentYear;
                $processedSemesters[$semKey] = true;

                if ($matchedUserId && $score360 > 0) {
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
                $projVal = floatval($rowData['PROYECTO SEMESTRE 1'] ?? $rowData['PROYECTO SEMESTRE 2'] ?? $rowData['PROYECTO'] ?? $rowData['IMPORTE'] ?? 0);
                
                $matchedUserId = $resolveOrCreateUser($empNum, $rawEmployeeName);
                $semKey = $semesterNum . '-' . $currentYear;
                $processedSemesters[$semKey] = true;

                if ($matchedUserId) {
                    $sqlProjUser = "UPDATE user_monthly_scorecards SET semester_project_1 = :proj WHERE user_id = :user_id AND semester = :semester";
                    if ($semesterNum === '2') {
                        $sqlProjUser = "UPDATE user_monthly_scorecards SET semester_project_2 = :proj WHERE user_id = :user_id AND semester = :semester";
                    }
                    $stmtPU = $db->prepare($sqlProjUser);
                    $stmtPU->execute([':proj' => $projVal, ':user_id' => $matchedUserId, ':semester' => $semKey]);
                }
            }
        }

        // 1. Guardar registros individuales en user_monthly_scorecards
        foreach ($individualRecords as $ind) {
            $uId = $ind['user_id'];
            $semKey = $ind['semester'];
            $mName = $ind['month_name'];
            $mIdx = $ind['month_index'];
            unset($ind['user_id'], $ind['semester'], $ind['month_name'], $ind['month_index']);

            $this->templateModel->updateUserMonthlyScorecard($semKey, $mName, $mIdx, $uId, $ind);
        }

        // 2. MÉTODO INTELIGENTE DE RECÁLCULO GLOBAL (IGNORANDO LOS CEROS DE USUARIOS INACTIVOS EN VENTAS)
        foreach (array_keys($processedSemesters) as $semKey) {
            $this->recalculateSemesterScorecardsMaster($db, $semKey);
        }

        return ['row_count' => max(1, $validRowCount)];
    }

    private function recalculateSemesterScorecardsMaster($db, $semesterKey) {
        $months = str_starts_with($semesterKey, '1-') 
            ? ['Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6]
            : ['Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12];

        foreach ($months as $monthName => $monthIndex) {
            $sqlAgg = "SELECT 
                        AVG(NULLIF(sales, 0)) as avg_sales,
                        AVG(NULLIF(performance, 0)) as avg_performance,
                        SUM(incidents) as sum_incidents,
                        AVG(NULLIF(attendance_habits, 0)) as avg_habits,
                        SUM(wrong_loadings) as sum_wrong_loadings,
                        AVG(NULLIF(six_steps, 0)) as avg_six_steps,
                        SUM(client_complaints) as sum_client_complaints,
                        SUM(wrong_charges) as sum_wrong_charges,
                        SUM(oils) as sum_oils,
                        SUM(invoices) as sum_invoices,
                        AVG(NULLIF(productivity_bonus, 0)) as avg_bonus,
                        SUM(absences) as sum_absences,
                        SUM(centenarios_received) as sum_cent_rec,
                        SUM(centenarios_redeemed) as sum_cent_red,
                        SUM(redemption_amount) as sum_red_amt,
                        AVG(NULLIF(eval_360, 0)) as avg_360,
                        SUM(semester_project_1) as sum_proj_1,
                        SUM(semester_project_2) as sum_proj_2
                       FROM user_monthly_scorecards 
                       WHERE semester = :semester AND month_name = :month_name";

            $stmtAgg = $db->prepare($sqlAgg);
            $stmtAgg->execute([':semester' => $semesterKey, ':month_name' => $monthName]);
            $agg = $stmtAgg->fetch(PDO::FETCH_ASSOC);

            if ($agg && $agg['avg_sales'] !== null) {
                $salesVal = floatval($agg['avg_sales']);
                if ($salesVal > 0 && $salesVal <= 1.0) $salesVal = $salesVal * 100;

                $perfVal = floatval($agg['avg_performance']);
                if ($perfVal > 0 && $perfVal <= 1.0) $perfVal = $perfVal * 100;

                $sqlCheck = "SELECT id FROM monthly_scorecards WHERE semester = :semester AND month_name = :month_name";
                $stmtChk = $db->prepare($sqlCheck);
                $stmtChk->execute([':semester' => $semesterKey, ':month_name' => $monthName]);
                $exists = $stmtChk->fetch(PDO::FETCH_ASSOC);

                if ($exists) {
                    $sqlUpd = "UPDATE monthly_scorecards SET 
                               sales = :sales,
                               performance = :performance,
                               incidents = :incidents,
                               attendance_habits = :habits,
                               wrong_loadings = :wrong_loadings,
                               six_steps = :six_steps,
                               client_complaints = :client_complaints,
                               wrong_charges = :wrong_charges,
                               oils = :oils,
                               invoices = :invoices,
                               productivity_bonus = :bonus,
                               absences = :absences,
                               centenarios_received = :cent_rec,
                               centenarios_redeemed = :cent_red,
                               redemption_amount = :red_amt,
                               eval_360 = :eval_360,
                               semester_project_1 = :proj_1,
                               semester_project_2 = :proj_2
                               WHERE semester = :semester AND month_name = :month_name";
                    $stmtU = $db->prepare($sqlUpd);
                    $stmtU->execute([
                        ':sales' => $salesVal,
                        ':performance' => $perfVal,
                        ':incidents' => $agg['sum_incidents'] ?? 0,
                        ':habits' => $agg['avg_habits'] ?? 0,
                        ':wrong_loadings' => $agg['sum_wrong_loadings'] ?? 0,
                        ':six_steps' => $agg['avg_six_steps'] ?? 0,
                        ':client_complaints' => $agg['sum_client_complaints'] ?? 0,
                        ':wrong_charges' => $agg['sum_wrong_charges'] ?? 0,
                        ':oils' => $agg['sum_oils'] ?? 0,
                        ':invoices' => $agg['sum_invoices'] ?? 0,
                        ':bonus' => $agg['avg_bonus'] ?? 0,
                        ':absences' => $agg['sum_absences'] ?? 0,
                        ':cent_rec' => $agg['sum_cent_rec'] ?? 0,
                        ':cent_red' => $agg['sum_cent_red'] ?? 0,
                        ':red_amt' => $agg['sum_red_amt'] ?? 0,
                        ':eval_360' => $agg['avg_360'] ?? 0,
                        ':proj_1' => $agg['sum_proj_1'] ?? 0,
                        ':proj_2' => $agg['sum_proj_2'] ?? 0,
                        ':semester' => $semesterKey,
                        ':month_name' => $monthName
                    ]);
                } else {
                    $sqlIns = "INSERT INTO monthly_scorecards 
                               (semester, month_name, month_index, sales, performance, incidents, attendance_habits, 
                                wrong_loadings, six_steps, client_complaints, wrong_charges, oils, invoices, 
                                productivity_bonus, absences, centenarios_received, centenarios_redeemed, 
                                redemption_amount, eval_360, semester_project_1, semester_project_2) 
                               VALUES 
                               (:semester, :month_name, :month_index, :sales, :performance, :incidents, :habits, 
                                :wrong_loadings, :six_steps, :client_complaints, :wrong_charges, :oils, :invoices, 
                                :bonus, :absences, :cent_rec, :cent_red, :red_amt, :eval_360, :proj_1, :proj_2)";
                    $stmtI = $db->prepare($sqlIns);
                    $stmtI->execute([
                        ':semester' => $semesterKey,
                        ':month_name' => $monthName,
                        ':month_index' => $monthIndex,
                        ':sales' => $salesVal,
                        ':performance' => $perfVal,
                        ':incidents' => $agg['sum_incidents'] ?? 0,
                        ':habits' => $agg['avg_habits'] ?? 0,
                        ':wrong_loadings' => $agg['sum_wrong_loadings'] ?? 0,
                        ':six_steps' => $agg['avg_six_steps'] ?? 0,
                        ':client_complaints' => $agg['sum_client_complaints'] ?? 0,
                        ':wrong_charges' => $agg['sum_wrong_charges'] ?? 0,
                        ':oils' => $agg['sum_oils'] ?? 0,
                        ':invoices' => $agg['sum_invoices'] ?? 0,
                        ':bonus' => $agg['avg_bonus'] ?? 0,
                        ':absences' => $agg['sum_absences'] ?? 0,
                        ':cent_rec' => $agg['sum_cent_rec'] ?? 0,
                        ':cent_red' => $agg['sum_cent_red'] ?? 0,
                        ':red_amt' => $agg['sum_red_amt'] ?? 0,
                        ':eval_360' => $agg['avg_360'] ?? 0,
                        ':proj_1' => $agg['sum_proj_1'] ?? 0,
                        ':proj_2' => $agg['sum_proj_2'] ?? 0
                    ]);
                }
            }
        }
    }
}
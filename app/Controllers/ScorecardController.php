<?php

require_once __DIR__ . '/../Models/Scorecard.php';
require_once __DIR__ . '/../Models/User.php';

class ScorecardController {
    private $scorecardModel;

    public function __construct() {
        Auth::check();
        $userRole = Auth::user()['role'] ?? '';
        $allowedRoles = ['General Admin', 'admin', 'Administrador General', 'Reportero Operativo', 'Reportero Administrativo', 'despachador'];
        
        if (!in_array($userRole, $allowedRoles)) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->scorecardModel = new Scorecard();
    }

    public function general() {
        $semesters = [
            '1-2025' => 'Semestre 1 – 2025',
            '2-2025' => 'Semestre 2 – 2025',
            '1-2026' => 'Semestre 1 – 2026',
            '2-2026' => 'Semestre 2 – 2026'
        ];

        $currentSemester = $_GET['semester'] ?? '1-2026';
        if (!array_key_exists($currentSemester, $semesters)) {
            $currentSemester = '1-2026';
        }

        $months = str_starts_with($currentSemester, '1-') 
            ? ['Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6]
            : ['Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12];

        $savedData = $this->scorecardModel->getBySemester($currentSemester);
        $summaryData = $this->scorecardModel->getSemesterSummary($currentSemester);

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/scorecard/general.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function individuals() {
        $db = Database::getInstance();

        // Procesar guardado vía POST al presionar el botón "Guardar"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $userId = $_POST['user_id'] ?? null;
            $semester = $_POST['semester'] ?? '1-2026';
            $statusFilter = $_POST['status'] ?? 'active';
            $limitParam = $_POST['limit'] ?? '50';
            
            if ($userId) {
                $this->scorecardModel->saveSemesterFinals($userId, $semester, $_POST);
                $_SESSION['success'] = 'Valores de la boleta individual guardados correctamente.';
            }

            header('Location: ' . BASE_URL . '/scorecard/individuals?user_id=' . $userId . '&semester=' . $semester . '&status=' . urlencode($statusFilter) . '&limit=' . urlencode($limitParam));
            exit;
        }

        $semesters = [
            '1-2025' => 'Semestre 1 – 2025',
            '2-2025' => 'Semestre 2 – 2025',
            '1-2026' => 'Semestre 1 – 2026',
            '2-2026' => 'Semestre 2 – 2026'
        ];

        $currentSemester = $_GET['semester'] ?? '1-2026';
        if (!array_key_exists($currentSemester, $semesters)) {
            $currentSemester = '1-2026';
        }

        $months = str_starts_with($currentSemester, '1-') 
            ? ['Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6]
            : ['Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12];

        $selectedUserId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        
        $selectedUser = null;
        $savedData = [];
        $summaryData = [];
        $generalSummaryData = [];
        $semesterFinals = [];

        if ($selectedUserId) {
            $userModel = new User();
            $selectedUser = $userModel->find($selectedUserId);

            if ($selectedUser) {
                $savedData = $this->scorecardModel->getUserScorecardBySemester($selectedUserId, $currentSemester);
                $summaryData = $this->scorecardModel->getUserSemesterSummary($selectedUserId, $currentSemester);
                $generalSummaryData = $this->scorecardModel->getSemesterSummary($currentSemester);
                $semesterFinals = $this->scorecardModel->getSemesterFinals($selectedUserId, $currentSemester);
            }
        }

        // Filtro de Estatus y Paginación (Soporta 5, 10, 15, 25, 50, all)
        $statusFilter = $_GET['status'] ?? 'active';
        $limitRaw = $_GET['limit'] ?? '50';
        $allowedLimits = ['5', '10', '15', '25', '50', 'all'];
        if (!in_array($limitRaw, $allowedLimits)) {
            $limitRaw = '50';
        }
        $limit = $limitRaw;

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        if ($limit === 'all') {
            $sqlLimit = 999999;
            $offset = 0;
        } else {
            $sqlLimit = (int)$limit;
            $offset = ($page - 1) * $sqlLimit;
        }

        // Consulta de despachadores
        $sqlWhere = " WHERE p.name LIKE :position_name";
        $params = [':position_name' => '%Despachador%'];

        if ($statusFilter === 'active') {
            $sqlWhere .= " AND u.status = 'active'";
        } elseif ($statusFilter === 'inactive') {
            $sqlWhere .= " AND u.status = 'inactive'";
        }

        $countSql = "SELECT COUNT(*) as total FROM users u JOIN positions p ON u.position_id = p.id" . $sqlWhere;
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)($stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        
        $totalPages = ($limit === 'all' || $sqlLimit <= 0) ? 1 : ceil($totalRows / $sqlLimit);
        if ($totalPages < 1) $totalPages = 1;

        $sqlList = "
            SELECT 
                u.id, 
                u.employee_number, 
                u.first_name, 
                u.last_name_paternal, 
                u.last_name_maternal,
                u.status,
                p.name AS position_name,
                CONCAT(s.first_name, ' ', s.last_name_paternal) AS supervisor_name
            FROM users u
            JOIN positions p ON u.position_id = p.id
            LEFT JOIN users s ON u.supervisor_id = s.id
            {$sqlWhere}
            ORDER BY u.first_name ASC, u.last_name_paternal ASC
            LIMIT {$sqlLimit} OFFSET {$offset}
        ";

        $stmtList = $db->prepare($sqlList);
        $stmtList->execute($params);
        $dispatchers = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        $csrfToken = CSRF::generateToken();

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/scorecard/individuals.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}
<?php

require_once __DIR__ . '/../Models/Scorecard.php';
require_once __DIR__ . '/../Models/User.php';

class ScorecardController {
    private $scorecardModel;

    public function __construct() {
        Auth::check();
        $userRole = Auth::user()['role'] ?? '';
        $allowedRoles = ['General Admin', 'admin', 'Administrador General', 'Reportero Operativo', 'Reportero Administrativo'];
        
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
        
        // Consultar a los usuarios activos cuyo PUESTO sea "Despachador"
        $stmtUsers = $db->prepare("
            SELECT u.id, u.employee_number, u.first_name, u.last_name_paternal 
            FROM users u
            JOIN positions p ON u.position_id = p.id
            WHERE u.status = 'active' AND p.name LIKE :position_name
            ORDER BY u.first_name ASC
        ");
        $stmtUsers->execute([':position_name' => '%Despachador%']);
        $activeUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $selectedUserId = $_GET['user_id'] ?? null;
        $selectedUserScorecards = [];

        if ($selectedUserId) {
            $stmt = $db->prepare("SELECT * FROM user_monthly_scorecards WHERE user_id = :user_id ORDER BY semester DESC, month_index ASC");
            $stmt->execute([':user_id' => $selectedUserId]);
            $selectedUserScorecards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../Views/scorecard/individuals.php';
    }
}
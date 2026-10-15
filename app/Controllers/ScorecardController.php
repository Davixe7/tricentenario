<?php

require_once __DIR__ . '/../Models/Scorecard.php';

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
        require_once __DIR__ . '/../Views/layout/header.php';
        echo '<div class="p-4"><h4>Módulo de Boletas Individuales en desarrollo</h4></div>';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}
<?php

class Scorecard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Obtener registros mensuales de la boleta general por semestre
    public function getBySemester($semester) {
        $sql = "SELECT * FROM monthly_scorecards WHERE semester = :semester ORDER BY month_index ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':semester' => $semester]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($rows as $row) {
            $data[$row['month_name']] = $row;
        }
        return $data;
    }

    // Calcular sumatorias y promedios semestrales para las tarjetas inferiores
    public function getSemesterSummary($semester) {
        $sql = "SELECT 
                SUM(centenarios_received) as total_centenarios_received,
                SUM(centenarios_redeemed) as total_centenarios_redeemed,
                SUM(redemption_amount) as total_redemption_amount,
                AVG(eval_360) as avg_eval_360,
                AVG(performance) as avg_performance,
                SUM(semester_project_1) as total_semester_project_1
                FROM monthly_scorecards WHERE semester = :semester";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':semester' => $semester]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [
            'total_centenarios_received' => 0,
            'total_centenarios_redeemed' => 0,
            'total_redemption_amount' => 0,
            'avg_eval_360' => 0,
            'avg_performance' => 0,
            'total_semester_project_1' => 0
        ];
    }
}
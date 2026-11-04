<?php

class Scorecard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

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

    public function getUserScorecardBySemester($userId, $semester) {
        $sql = "SELECT * FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester ORDER BY month_index ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':semester' => $semester
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $data[$row['month_name']] = $row;
        }
        return $data;
    }

    public function getUserSemesterSummary($userId, $semester) {
        $sql = "SELECT 
                SUM(centenarios_received) as total_centenarios_received,
                SUM(centenarios_redeemed) as total_centenarios_redeemed,
                SUM(redemption_amount) as total_redemption_amount,
                AVG(eval_360) as avg_eval_360,
                AVG(performance) as avg_performance,
                SUM(semester_project_1) as total_semester_project_1
                FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':semester' => $semester
        ]);
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

    public function getSemesterFinals($userId, $semester) {
        $sql = "SELECT * FROM user_semester_finals WHERE user_id = :user_id AND semester = :semester LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':semester' => $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveSemesterFinals($userId, $semester, $data) {
        // Limpieza de caracteres de formato ($, %, comas y espacios)
        $cleanNum = function($val) {
            if ($val === null || $val === '') return 0;
            $val = str_replace(['$', '%', ',', ' '], '', (string)$val);
            return is_numeric($val) ? floatval($val) : 0;
        };

        $sql = "INSERT INTO user_semester_finals (
            user_id, semester, final_sales, final_performance, final_incidents, 
            final_attendance_habits, final_wrong_loadings, final_six_steps, 
            final_client_complaints, final_wrong_charges, final_oils, final_invoices, 
            final_productivity_bonus, final_absences, final_qualification, final_semester_project
        ) VALUES (
            :user_id, :semester, :final_sales, :final_performance, :final_incidents, 
            :final_attendance_habits, :final_wrong_loadings, :final_six_steps, 
            :final_client_complaints, :final_wrong_charges, :final_oils, :final_invoices, 
            :final_productivity_bonus, :final_absences, :final_qualification, :final_semester_project
        ) ON DUPLICATE KEY UPDATE 
            final_sales = VALUES(final_sales),
            final_performance = VALUES(final_performance),
            final_incidents = VALUES(final_incidents),
            final_attendance_habits = VALUES(final_attendance_habits),
            final_wrong_loadings = VALUES(final_wrong_loadings),
            final_six_steps = VALUES(final_six_steps),
            final_client_complaints = VALUES(final_client_complaints),
            final_wrong_charges = VALUES(final_wrong_charges),
            final_oils = VALUES(final_oils),
            final_invoices = VALUES(final_invoices),
            final_productivity_bonus = VALUES(final_productivity_bonus),
            final_absences = VALUES(final_absences),
            final_qualification = VALUES(final_qualification),
            final_semester_project = VALUES(final_semester_project)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':semester' => $semester,
            ':final_sales' => $cleanNum($data['final_sales'] ?? 0),
            ':final_performance' => $cleanNum($data['final_performance'] ?? 0),
            ':final_incidents' => $cleanNum($data['final_incidents'] ?? 0),
            ':final_attendance_habits' => $cleanNum($data['final_attendance_habits'] ?? 0),
            ':final_wrong_loadings' => $cleanNum($data['final_wrong_loadings'] ?? 0),
            ':final_six_steps' => $cleanNum($data['final_six_steps'] ?? 0),
            ':final_client_complaints' => $cleanNum($data['final_client_complaints'] ?? 0),
            ':final_wrong_charges' => $cleanNum($data['final_wrong_charges'] ?? 0),
            ':final_oils' => $cleanNum($data['final_oils'] ?? 0),
            ':final_invoices' => $cleanNum($data['final_invoices'] ?? 0),
            ':final_productivity_bonus' => $cleanNum($data['final_productivity_bonus'] ?? 0),
            ':final_absences' => $cleanNum($data['final_absences'] ?? 0),
            ':final_qualification' => $cleanNum($data['final_qualification'] ?? 0),
            ':final_semester_project' => $cleanNum($data['final_semester_project'] ?? 0)
        ]);
    }
}
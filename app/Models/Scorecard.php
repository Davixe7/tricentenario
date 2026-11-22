<?php

class Scorecard {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Calcula los indicadores mensuales de la Boleta General directamente
     * desde user_monthly_scorecards (Single Source of Truth), incluyendo
     * ceros de colaboradores del reporte mensual y excluyendo filas vacías
     * generadas únicamente por 360° o bitácora de incidencias.
     */
    public function getBySemester($semester) {
        $hasReportCond = "(
            ums.sales > 0 OR ums.performance > 0 OR ums.oils > 0 OR
            ums.attendance_habits > 0 OR ums.six_steps > 0 OR
            ums.wrong_loadings > 0 OR ums.client_complaints > 0 OR
            ums.wrong_charges > 0 OR ums.invoices > 0 OR
            ums.productivity_bonus > 0 OR ums.absences > 0 OR
            ums.centenarios_received > 0 OR ums.centenarios_redeemed > 0 OR
            ums.redemption_amount > 0
        )";

        $sql = "SELECT
                    ums.semester,
                    ums.month_name,
                    ums.month_index,
                    AVG(CASE WHEN {$hasReportCond} THEN ums.sales END) AS sales,
                    AVG(CASE WHEN {$hasReportCond} THEN ums.performance END) AS performance,
                    SUM(ums.incidents) AS incidents,
                    AVG(CASE WHEN {$hasReportCond} THEN ums.attendance_habits END) AS attendance_habits,
                    SUM(ums.wrong_loadings) AS wrong_loadings,
                    AVG(CASE WHEN {$hasReportCond} THEN ums.six_steps END) AS six_steps,
                    SUM(ums.client_complaints) AS client_complaints,
                    SUM(ums.wrong_charges) AS wrong_charges,
                    SUM(ums.oils) AS oils,
                    SUM(ums.invoices) AS invoices,
                    AVG(CASE WHEN {$hasReportCond} THEN ums.productivity_bonus END) AS productivity_bonus,
                    SUM(ums.absences) AS absences,
                    SUM(ums.centenarios_received) AS centenarios_received,
                    SUM(ums.centenarios_redeemed) AS centenarios_redeemed,
                    SUM(ums.redemption_amount) AS redemption_amount,
                    AVG(NULLIF(ums.eval_360, 0)) AS eval_360,
                    SUM(ums.semester_project_1) AS semester_project_1,
                    SUM(ums.semester_project_2) AS semester_project_2
                FROM user_monthly_scorecards ums
                WHERE ums.semester = :semester
                GROUP BY ums.semester, ums.month_name, ums.month_index
                ORDER BY ums.month_index ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':semester' => $semester]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $row['sales'] = $row['sales'] !== null ? round((float)$row['sales'], 2) : 0.0;
            $row['performance'] = $row['performance'] !== null ? round((float)$row['performance'], 2) : 0.0;
            $row['attendance_habits'] = $row['attendance_habits'] !== null ? round((float)$row['attendance_habits'], 2) : 0.0;
            $row['six_steps'] = $row['six_steps'] !== null ? round((float)$row['six_steps'], 2) : 0.0;
            $row['productivity_bonus'] = $row['productivity_bonus'] !== null ? round((float)$row['productivity_bonus'], 2) : 0.0;
            $row['eval_360'] = $row['eval_360'] !== null ? round((float)$row['eval_360'], 2) : 0.0;
            $data[$row['month_name']] = $row;
        }
        return $data;
    }

    /**
     * Resumen semestral computado a partir de los meses calculados y
     * las calificaciones/proyectos semestrales en user_semester_finals.
     */
    public function getSemesterSummary($semester) {
        $monthlyRows = $this->getBySemester($semester);

        $monthsCount = count($monthlyRows);
        $sumSales = 0.0;
        $sumPerf = 0.0;
        $totalIncidents = 0;
        $totalCentRec = 0;
        $totalCentRed = 0;
        $totalRedAmt = 0.0;

        foreach ($monthlyRows as $m) {
            $sumSales += (float)($m['sales'] ?? 0);
            $sumPerf += (float)($m['performance'] ?? 0);
            $totalIncidents += (int)($m['incidents'] ?? 0);
            $totalCentRec += (int)($m['centenarios_received'] ?? 0);
            $totalCentRed += (int)($m['centenarios_redeemed'] ?? 0);
            $totalRedAmt += (float)($m['redemption_amount'] ?? 0);
        }

        $sqlProj = "SELECT COALESCE(SUM(final_semester_project), 0) AS total_proj
                    FROM user_semester_finals
                    WHERE semester = :semester";
        $stmtProj = $this->db->prepare($sqlProj);
        $stmtProj->execute([':semester' => $semester]);
        $projRow = $stmtProj->fetch(PDO::FETCH_ASSOC);
        $totalProj = round((float)($projRow['total_proj'] ?? 0), 2);

        $sql360 = "SELECT AVG(NULLIF(ums.eval_360, 0)) AS avg_eval_360
                   FROM user_monthly_scorecards ums
                   WHERE ums.semester = :semester";
        $stmt360 = $this->db->prepare($sql360);
        $stmt360->execute([':semester' => $semester]);
        $row360 = $stmt360->fetch(PDO::FETCH_ASSOC);
        $avg360 = ($row360 && $row360['avg_eval_360'] !== null) ? round((float)$row360['avg_eval_360'], 2) : 0.0;

        $sqlFinalQual = "SELECT AVG(final_qualification) AS avg_final_qual
                         FROM user_semester_finals
                         WHERE semester = :semester";
        $stmtQual = $this->db->prepare($sqlFinalQual);
        $stmtQual->execute([':semester' => $semester]);
        $qualRes = $stmtQual->fetch(PDO::FETCH_ASSOC);

        $avgFinalQual = ($qualRes && $qualRes['avg_final_qual'] !== null)
            ? round((float)$qualRes['avg_final_qual'], 2)
            : 0.0;

        return [
            'avg_sales' => $monthsCount > 0 ? round($sumSales / $monthsCount, 2) : 0.0,
            'avg_performance' => $monthsCount > 0 ? round($sumPerf / $monthsCount, 2) : 0.0,
            'total_incidents' => $totalIncidents,
            'total_centenarios_received' => $totalCentRec,
            'total_centenarios_redeemed' => $totalCentRed,
            'total_redemption_amount' => round($totalRedAmt, 2),
            'avg_eval_360' => $avg360,
            'total_semester_project_1' => $totalProj,
            'total_semester_project_2' => $totalProj,
            'avg_final_qualification' => $avgFinalQual
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
                AVG(sales) as avg_sales,
                AVG(performance) as avg_performance,
                SUM(incidents) as total_incidents,
                SUM(centenarios_received) as total_centenarios_received,
                SUM(centenarios_redeemed) as total_centenarios_redeemed,
                SUM(redemption_amount) as total_redemption_amount,
                AVG(eval_360) as avg_eval_360
                FROM user_monthly_scorecards WHERE user_id = :user_id AND semester = :semester";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':semester' => $semester
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $sqlProj = "SELECT COALESCE(final_semester_project, 0) AS proj_val
                    FROM user_semester_finals
                    WHERE user_id = :user_id AND semester = :semester
                    LIMIT 1";
        $stmtProj = $this->db->prepare($sqlProj);
        $stmtProj->execute([
            ':user_id' => $userId,
            ':semester' => $semester
        ]);
        $projRow = $stmtProj->fetch(PDO::FETCH_ASSOC);
        $projVal = (float)($projRow['proj_val'] ?? 0);

        return [
            'avg_sales' => $result['avg_sales'] ?? 0,
            'avg_performance' => $result['avg_performance'] ?? 0,
            'total_incidents' => $result['total_incidents'] ?? 0,
            'total_centenarios_received' => $result['total_centenarios_received'] ?? 0,
            'total_centenarios_redeemed' => $result['total_centenarios_redeemed'] ?? 0,
            'total_redemption_amount' => $result['total_redemption_amount'] ?? 0,
            'avg_eval_360' => $result['avg_eval_360'] ?? 0,
            'total_semester_project_1' => $projVal,
            'total_semester_project_2' => $projVal
        ];
    }

    public function getSemesterFinals($userId, $semester) {
        $sql = "SELECT * FROM user_semester_finals WHERE user_id = :user_id AND semester = :semester LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':semester' => $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveSemesterFinals($userId, $semester, $data) {
        $cleanNum = function($val) {
            if ($val === null || $val === '') return 0;
            $s = str_replace(['$', '%', ' '], '', trim((string)$val));
            if ($s === '' || $s === '-') return 0;

            if (strpos($s, ',') !== false) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif (substr_count($s, '.') > 1) {
                $parts = explode('.', $s);
                $dec = array_pop($parts);
                $s = implode('', $parts) . '.' . $dec;
            }

            return is_numeric($s) ? floatval($s) : 0;
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
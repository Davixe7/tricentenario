<?php

class Template {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getLastLog($type, $semester = null) {
        $sql = "SELECT tl.*, u.first_name, u.last_name_paternal 
                FROM template_logs tl
                LEFT JOIN users u ON tl.user_id = u.id
                WHERE tl.template_type = :type";
        
        $params = [':type' => $type];
        if ($semester !== null) {
            $sql .= " AND tl.semester = :semester";
            $params[':semester'] = $semester;
        }

        $sql .= " ORDER BY tl.created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveLog($type, $recordCount, $filePath, $userId, $semester = null) {
        $sql = "INSERT INTO template_logs (template_type, semester, record_count, file_path, user_id) 
                VALUES (:type, :semester, :record_count, :file_path, :user_id)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':type' => $type,
            ':semester' => $semester,
            ':record_count' => $recordCount,
            ':file_path' => $filePath,
            ':user_id' => $userId
        ]);
    }

    public function updateMonthlyScorecardFromData($semesterKey, $monthName, $monthIndex, $data) {
        $sqlCheck = "SELECT id FROM monthly_scorecards WHERE semester = :semester AND month_name = :month_name";
        $stmt = $this->db->prepare($sqlCheck);
        $stmt->execute([':semester' => $semesterKey, ':month_name' => $monthName]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $sql = "UPDATE monthly_scorecards SET 
                    sales = COALESCE(:sales, sales),
                    performance = COALESCE(:performance, performance),
                    incidents = COALESCE(:incidents, incidents),
                    attendance_habits = COALESCE(:attendance_habits, attendance_habits),
                    wrong_loadings = COALESCE(:wrong_loadings, wrong_loadings),
                    six_steps = COALESCE(:six_steps, six_steps),
                    client_complaints = COALESCE(:client_complaints, client_complaints),
                    wrong_charges = COALESCE(:wrong_charges, wrong_charges),
                    oils = COALESCE(:oils, oils),
                    invoices = COALESCE(:invoices, invoices),
                    productivity_bonus = COALESCE(:productivity_bonus, productivity_bonus),
                    absences = COALESCE(:absences, absences),
                    centenarios_received = COALESCE(:centenarios_received, centenarios_received),
                    centenarios_redeemed = COALESCE(:centenarios_redeemed, centenarios_redeemed),
                    redemption_amount = COALESCE(:redemption_amount, redemption_amount),
                    eval_360 = COALESCE(:eval_360, eval_360),
                    semester_project_1 = COALESCE(:semester_project_1, semester_project_1)
                    WHERE semester = :semester AND month_name = :month_name";

            $params = [
                ':semester' => $semesterKey,
                ':month_name' => $monthName,
                ':sales' => $data['sales'] ?? null,
                ':performance' => $data['performance'] ?? null,
                ':incidents' => $data['incidents'] ?? null,
                ':attendance_habits' => $data['attendance_habits'] ?? null,
                ':wrong_loadings' => $data['wrong_loadings'] ?? null,
                ':six_steps' => $data['six_steps'] ?? null,
                ':client_complaints' => $data['client_complaints'] ?? null,
                ':wrong_charges' => $data['wrong_charges'] ?? null,
                ':oils' => $data['oils'] ?? null,
                ':invoices' => $data['invoices'] ?? null,
                ':productivity_bonus' => $data['productivity_bonus'] ?? null,
                ':absences' => $data['absences'] ?? null,
                ':centenarios_received' => $data['centenarios_received'] ?? null,
                ':centenarios_redeemed' => $data['centenarios_redeemed'] ?? null,
                ':redemption_amount' => $data['redemption_amount'] ?? null,
                ':eval_360' => $data['eval_360'] ?? null,
                ':semester_project_1' => $data['semester_project_1'] ?? null
            ];
        } else {
            $sql = "INSERT INTO monthly_scorecards 
                    (semester, month_name, month_index, sales, performance, incidents, attendance_habits, 
                     wrong_loadings, six_steps, client_complaints, wrong_charges, oils, 
                     invoices, productivity_bonus, absences, centenarios_received, centenarios_redeemed, 
                     redemption_amount, eval_360, semester_project_1) 
                    VALUES 
                    (:semester, :month_name, :month_index, :sales, :performance, :incidents, :attendance_habits, 
                     :wrong_loadings, :six_steps, :client_complaints, :wrong_charges, :oils, 
                     :invoices, :productivity_bonus, :absences, :centenarios_received, :centenarios_redeemed, 
                     :redemption_amount, :eval_360, :semester_project_1)";

            $params = [
                ':semester' => $semesterKey,
                ':month_name' => $monthName,
                ':month_index' => $monthIndex,
                ':sales' => $data['sales'] ?? 0,
                ':performance' => $data['performance'] ?? 0,
                ':incidents' => $data['incidents'] ?? 0,
                ':attendance_habits' => $data['attendance_habits'] ?? 0,
                ':wrong_loadings' => $data['wrong_loadings'] ?? 0,
                ':six_steps' => $data['six_steps'] ?? 0,
                ':client_complaints' => $data['client_complaints'] ?? 0,
                ':wrong_charges' => $data['wrong_charges'] ?? 0,
                ':oils' => $data['oils'] ?? 0,
                ':invoices' => $data['invoices'] ?? 0,
                ':productivity_bonus' => $data['productivity_bonus'] ?? 0,
                ':absences' => $data['absences'] ?? 0,
                ':centenarios_received' => $data['centenarios_received'] ?? 0,
                ':centenarios_redeemed' => $data['centenarios_redeemed'] ?? 0,
                ':redemption_amount' => $data['redemption_amount'] ?? 0,
                ':eval_360' => $data['eval_360'] ?? 0,
                ':semester_project_1' => $data['semester_project_1'] ?? 0
            ];
        }

        $stmtSave = $this->db->prepare($sql);
        return $stmtSave->execute($params);
    }
}
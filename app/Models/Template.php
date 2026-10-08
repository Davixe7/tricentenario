<?php

class Template {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Obtener la última actualización por tipo y semestre (si aplica)
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

    // Registrar la carga de una plantilla
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
}
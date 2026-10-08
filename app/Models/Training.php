<?php

class Training {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllWithUserCount() {
        $sql = "SELECT t.*, COUNT(ut.user_id) AS total_users 
                FROM trainings t
                LEFT JOIN user_trainings ut ON t.id = ut.training_id
                GROUP BY t.id
                ORDER BY t.training_date DESC, t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $sql = "SELECT * FROM trainings WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAssignedUsers($trainingId) {
        $sql = "SELECT u.id, u.first_name, u.last_name_paternal, u.last_name_maternal, u.employee_number, ut.id as user_training_id, ut.attachment_path
                FROM user_trainings ut
                INNER JOIN users u ON ut.user_id = u.id
                WHERE ut.training_id = ?
                ORDER BY u.last_name_paternal ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$trainingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createWithUsers($data, $userIds = []) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO trainings (title, type, training_date, duration_hours) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['type'],
                $data['training_date'],
                $data['duration_hours'] ?? null
            ]);
            $trainingId = $this->db->lastInsertId();

            if (!empty($userIds)) {
                $sqlUser = "INSERT INTO user_trainings (user_id, training_id) VALUES (?, ?)";
                $stmtUser = $this->db->prepare($sqlUser);
                foreach ($userIds as $userId) {
                    $stmtUser->execute([$userId, $trainingId]);
                }
            }

            $this->db->commit();
            return $trainingId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function assignToUser($userId, $trainingId, $attachmentPath = null) {
        $sql = "INSERT INTO user_trainings (user_id, training_id, attachment_path) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $trainingId, $attachmentPath]);
    }

    public function getByUser($userId) {
        $sql = "SELECT ut.id AS user_training_id, t.id AS training_id, t.title, t.type, t.training_date, t.duration_hours, ut.attachment_path, ut.created_at
                FROM user_trainings ut
                INNER JOIN trainings t ON ut.training_id = t.id
                WHERE ut.user_id = ?
                ORDER BY t.training_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAttachment($userTrainingId, $attachmentPath) {
        $sql = "UPDATE user_trainings SET attachment_path = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$attachmentPath, $userTrainingId]);
    }

    public function removeUserTraining($userTrainingId) {
        $sql = "DELETE FROM user_trainings WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userTrainingId]);
    }

    public function delete($id) {
        $sql = "DELETE FROM trainings WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $username
        ]);
        return $stmt->fetch();
    }

    public function findByEmailOrUsername($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (email = :email OR username = :username) AND status = 'active' LIMIT 1");
        $stmt->execute([
            ':email'    => $identifier,
            ':username' => $identifier
        ]);
        return $stmt->fetch();
    }

    public function saveResetToken($userId, $token, $expires) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id");
        return $stmt->execute([
            ':token'   => $token,
            ':expires' => $expires,
            ':id'      => $userId
        ]);
    }

    public function findByResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function updatePasswordAndClearToken($userId, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $userId
        ]);
    }

    public function getAll($statusFilter = null, $search = null) {
        $sql = "SELECT u.*, a.name AS area_name, p.name AS position_name 
                FROM users u
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $sql .= " AND u.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (
                u.first_name LIKE :s1 
                OR u.last_name_paternal LIKE :s2 
                OR u.last_name_maternal LIKE :s3 
                OR u.employee_number LIKE :s4 
                OR u.username LIKE :s5 
                OR u.email LIKE :s6 
                OR u.curp LIKE :s7 
                OR u.rfc LIKE :s8
            )";
            $searchTerm = '%' . $search . '%';
            for ($i = 1; $i <= 8; $i++) {
                $params[":s$i"] = $searchTerm;
            }
        }

        $sql .= " ORDER BY u.first_name ASC, u.last_name_paternal ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO users (
            employee_number, first_name, last_name_paternal, last_name_maternal, username, password,
            email, role, area_id, position_id, supervisor_id, counterpart_reporter_id, phone, nss,
            rfc, curp, birth_date, address, avatar, status
        ) VALUES (
            :employee_number, :first_name, :last_name_paternal, :last_name_maternal, :username, :password,
            :email, :role, :area_id, :position_id, :supervisor_id, :counterpart_reporter_id, :phone, :nss,
            :rfc, :curp, :birth_date, :address, :avatar, :status
        )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        unset($data['id']);
        $fields = [];
        foreach ($data as $key => $val) {
            $fields[] = "$key = :$key";
        }
        $data['id'] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'inactive' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- DOCUMENTOS OFICIALES ---

    public function getDocumentsByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_documents WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getDocumentById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user_documents WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function addDocument($userId, $description, $filePath) {
        $stmt = $this->db->prepare("INSERT INTO user_documents (user_id, description, file_path) VALUES (:user_id, :description, :file_path)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':description' => $description,
            ':file_path' => $filePath
        ]);
    }

    public function deleteDocument($id) {
        $stmt = $this->db->prepare("DELETE FROM user_documents WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- TRAYECTORIA ---

    public function getTrajectoriesByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_trajectories WHERE user_id = :user_id ORDER BY movement_date DESC, id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getTrajectoryById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user_trajectories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function addTrajectory($userId, $movement, $movementDate, $rehireable, $notes) {
        $stmt = $this->db->prepare("INSERT INTO user_trajectories (user_id, movement, movement_date, rehireable, notes) VALUES (:user_id, :movement, :movement_date, :rehireable, :notes)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':movement' => $movement,
            ':movement_date' => $movementDate,
            ':rehireable' => $rehireable,
            ':notes' => $notes
        ]);
    }

    public function deleteTrajectory($id) {
        $stmt = $this->db->prepare("DELETE FROM user_trajectories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
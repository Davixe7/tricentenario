<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function getAll($statusFilter = null) {
        $sql = "SELECT u.*, a.name AS area_name, p.name AS position_name 
                FROM users u
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN positions p ON u.position_id = p.id";
        
        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " WHERE u.status = :status";
        }
        $sql .= " ORDER BY u.first_name ASC, u.last_name_paternal ASC";

        $stmt = $this->db->prepare($sql);
        if ($statusFilter && $statusFilter !== 'all') {
            $stmt->bindValue(':status', $statusFilter);
        }
        $stmt->execute();
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
}
<?php
class Area {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($statusFilter = null, $search = null) {
        $sql = "SELECT * FROM areas WHERE 1=1";
        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM areas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($name, $status) {
        $stmt = $this->db->prepare("INSERT INTO areas (name, status) VALUES (:name, :status)");
        return $stmt->execute([':name' => $name, ':status' => $status]);
    }

    public function update($id, $name, $status) {
        $stmt = $this->db->prepare("UPDATE areas SET name = :name, status = :status WHERE id = :id");
        return $stmt->execute([':name' => $name, ':status' => $status, ':id' => $id]);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE areas SET status = 'inactive' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
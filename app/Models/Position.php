<?php
class Position {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($statusFilter = null) {
        $sql = "SELECT * FROM positions";
        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " WHERE status = :status";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        if ($statusFilter && $statusFilter !== 'all') {
            $stmt->bindValue(':status', $statusFilter);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM positions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($name, $status) {
        $stmt = $this->db->prepare("INSERT INTO positions (name, status) VALUES (:name, :status)");
        return $stmt->execute([':name' => $name, ':status' => $status]);
    }

    public function update($id, $name, $status) {
        $stmt = $this->db->prepare("UPDATE positions SET name = :name, status = :status WHERE id = :id");
        return $stmt->execute([':name' => $name, ':status' => $status, ':id' => $id]);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE positions SET status = 'inactive' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
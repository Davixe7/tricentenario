<?php
require_once __DIR__ . '/../Models/Area.php';

class AreaController {
    private $areaModel;

    public function __construct() {
        Auth::check();
        $this->areaModel = new Area();
    }

    public function index() {
        $statusFilter = $_GET['status'] ?? 'active';
        $searchFilter = trim($_GET['search'] ?? ($_GET['q'] ?? ''));
        $areas = $this->areaModel->getAll($statusFilter, $searchFilter);
        require_once __DIR__ . '/../Views/areas/index.php';
    }

    public function create() {
        $area = null;
        require_once __DIR__ . '/../Views/areas/form.php';
    }

    public function store() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($name)) {
            $this->areaModel->create($name, $status);
        }

        header('Location: ' . BASE_URL . '/areas');
        exit;
    }

    public function edit($id) {
        $area = $this->areaModel->find($id);
        if (!$area) {
            header('Location: ' . BASE_URL . '/areas');
            exit;
        }
        require_once __DIR__ . '/../Views/areas/form.php';
    }

    public function update($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($name)) {
            $this->areaModel->update($id, $name, $status);
        }

        header('Location: ' . BASE_URL . '/areas');
        exit;
    }

    public function delete($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $this->areaModel->softDelete($id);
        header('Location: ' . BASE_URL . '/areas');
        exit;
    }
}
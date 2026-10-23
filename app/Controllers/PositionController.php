<?php
require_once __DIR__ . '/../Models/Position.php';

class PositionController {
    private $positionModel;

    public function __construct() {
        Auth::check();
        $this->positionModel = new Position();
    }

    public function index() {
        $statusFilter = $_GET['status'] ?? 'active';
        $searchFilter = trim($_GET['search'] ?? ($_GET['q'] ?? ''));
        $positions = $this->positionModel->getAll($statusFilter, $searchFilter);
        require_once __DIR__ . '/../Views/positions/index.php';
    }

    public function create() {
        $position = null;
        require_once __DIR__ . '/../Views/positions/form.php';
    }

    public function store() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($name)) {
            try {
                $this->positionModel->create($name, $status);
                $_SESSION['success'] = 'Puesto creado exitosamente.';
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'No se pudo crear el puesto. Es posible que el nombre ya exista.';
            }
        } else {
            $_SESSION['error'] = 'El nombre del puesto es obligatorio.';
        }

        header('Location: ' . BASE_URL . '/positions');
        exit;
    }

    public function edit($id) {
        $position = $this->positionModel->find($id);
        if (!$position) {
            header('Location: ' . BASE_URL . '/positions');
            exit;
        }
        require_once __DIR__ . '/../Views/positions/form.php';
    }

    public function update($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!empty($name)) {
            try {
                $this->positionModel->update($id, $name, $status);
                $_SESSION['success'] = 'Puesto actualizado exitosamente.';
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Error al actualizar el puesto. Es posible que el nombre ya exista.';
            }
        } else {
            $_SESSION['error'] = 'El nombre del puesto no puede estar vacío.';
        }

        header('Location: ' . BASE_URL . '/positions');
        exit;
    }

    public function delete($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $this->positionModel->softDelete($id);
        $_SESSION['success'] = 'Puesto desactivado correctamente.';
        header('Location: ' . BASE_URL . '/positions');
        exit;
    }
}
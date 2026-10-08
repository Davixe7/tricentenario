<?php

require_once __DIR__ . '/../Models/Training.php';
require_once __DIR__ . '/../Models/User.php';

class TrainingController {
    private $trainingModel;
    private $userModel;

    public function __construct() {
        Auth::check();
        $this->trainingModel = new Training();
        $this->userModel = new User();
    }

    public function index() {
        $trainings = $this->trainingModel->getAllWithUserCount();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/trainings/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function create() {
        $users = $this->userModel->getAll('active');
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/trainings/form.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $durationHours = trim($_POST['duration_hours'] ?? '');

            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'type' => $_POST['type'] ?? 'Curso',
                'training_date' => $_POST['training_date'] ?? date('Y-m-d'),
                'duration_hours' => $durationHours !== '' ? $durationHours : null
            ];

            $userIds = $_POST['users'] ?? [];

            if (!empty($data['title']) && !empty($data['training_date'])) {
                $this->trainingModel->createWithUsers($data, $userIds);
                header('Location: ' . BASE_URL . '/trainings?success=1');
                exit();
            }
        }
        header('Location: ' . BASE_URL . '/trainings/create?error=1');
        exit();
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/trainings');
            exit();
        }

        $training = $this->trainingModel->find($id);
        $assignedUsers = $this->trainingModel->getAssignedUsers($id);

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/trainings/show.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function delete() {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $this->trainingModel->delete($id);
        }
        header('Location: ' . BASE_URL . '/trainings?deleted=1');
        exit();
    }
}
<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Area.php';
require_once __DIR__ . '/../Models/Position.php';
require_once __DIR__ . '/../Models/Training.php';

class UserController {
    private $userModel;
    private $areaModel;
    private $positionModel;
    private $trainingModel;

    public function __construct() {
        Auth::check();
        $this->userModel = new User();
        $this->areaModel = new Area();
        $this->positionModel = new Position();
        $this->trainingModel = new Training();
    }

    public function index() {
        $statusFilter = $_GET['status'] ?? 'active';
        $searchFilter = trim($_GET['q'] ?? ($_GET['search'] ?? ''));
        
        // Paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $totalUsers = $this->userModel->getTotalCount($statusFilter, $searchFilter);
        $totalPages = ceil($totalUsers / $perPage);
        if ($totalPages < 1) $totalPages = 1;

        $users = $this->userModel->getAll($statusFilter, $searchFilter, $perPage, $offset);
        
        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function create() {
        $user = null;
        $areas = $this->areaModel->getAll('active');
        $positions = $this->positionModel->getAll('active');
        $activeUsers = $this->userModel->getAll('active', null, 1000, 0);
        
        // Al crear un usuario nuevo, todos los activos están disponibles como contraparte
        $counterpartUsers = $activeUsers;

        $documents = [];
        $trajectories = [];
        $additionalInfos = [];
        $userTrainings = [];
        $allTrainings = $this->trainingModel->getAllWithUserCount();
        require_once __DIR__ . '/../Views/users/form.php';
    }

    public function store() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $avatarName = null;
        if (!empty($_FILES['avatar']['name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatarName = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], UPLOAD_DIR . $avatarName);
        }

        $data = [
            'employee_number' => !empty($_POST['employee_number']) ? (int)$_POST['employee_number'] : null,
            'first_name' => trim($_POST['first_name']),
            'last_name_paternal' => trim($_POST['last_name_paternal']),
            'last_name_maternal' => trim($_POST['last_name_maternal'] ?? ''),
            'username' => trim($_POST['username']),
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'],
            'area_id' => !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null,
            'position_id' => !empty($_POST['position_id']) ? (int)$_POST['position_id'] : null,
            'supervisor_id' => !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null,
            'counterpart_reporter_id' => !empty($_POST['counterpart_reporter_id']) ? (int)$_POST['counterpart_reporter_id'] : null,
            'phone' => trim($_POST['phone'] ?? ''),
            'nss' => trim($_POST['nss'] ?? ''),
            'rfc' => trim($_POST['rfc'] ?? ''),
            'curp' => trim($_POST['curp'] ?? ''),
            'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
            'address' => trim($_POST['address'] ?? ''),
            'avatar' => $avatarName,
            'status' => $_POST['status'] ?? 'active'
        ];

        $this->userModel->create($data);
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    public function edit($id) {
        $user = $this->userModel->find($id);
        if (!$user) {
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        $areas = $this->areaModel->getAll('active');
        $positions = $this->positionModel->getAll('active');
        $activeUsers = $this->userModel->getAll('active', null, 1000, 0);

        // Excluir al propio usuario que se está editando para que no pueda ser su propia contraparte
        $counterpartUsers = array_filter($activeUsers, function($u) use ($id) {
            return $u['id'] != $id;
        });

        $documents = $this->userModel->getDocumentsByUserId($id);
        $trajectories = $this->userModel->getTrajectoriesByUserId($id);
        $additionalInfos = $this->userModel->getAdditionalInfosByUserId($id);
        
        $userTrainings = $this->trainingModel->getByUser($id);
        $allTrainings = $this->trainingModel->getAllWithUserCount();

        require_once __DIR__ . '/../Views/users/form.php';
    }

    public function update($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $data = [
            'employee_number' => !empty($_POST['employee_number']) ? (int)$_POST['employee_number'] : null,
            'first_name' => trim($_POST['first_name']),
            'last_name_paternal' => trim($_POST['last_name_paternal']),
            'last_name_maternal' => trim($_POST['last_name_maternal'] ?? ''),
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'],
            'area_id' => !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null,
            'position_id' => !empty($_POST['position_id']) ? (int)$_POST['position_id'] : null,
            'supervisor_id' => !empty($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : null,
            'counterpart_reporter_id' => !empty($_POST['counterpart_reporter_id']) ? (int)$_POST['counterpart_reporter_id'] : null,
            'phone' => trim($_POST['phone'] ?? ''),
            'nss' => trim($_POST['nss'] ?? ''),
            'rfc' => trim($_POST['rfc'] ?? ''),
            'curp' => trim($_POST['curp'] ?? ''),
            'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
            'address' => trim($_POST['address'] ?? ''),
            'status' => $_POST['status'] ?? 'active'
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        if (!empty($_FILES['avatar']['name'])) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatarName = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], UPLOAD_DIR . $avatarName);
            $data['avatar'] = $avatarName;
        }

        $this->userModel->update($id, $data);
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    public function delete($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $this->userModel->softDelete($id);
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    // --- ACCIONES DOCUMENTOS OFICIALES ---

    public function uploadDocument() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $description = trim($_POST['description'] ?? '');

        if ($userId && $description && isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
            $filename = 'doc_' . $userId . '_' . time() . '.' . $ext;

            $uploadDir = UPLOAD_DIR . 'documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $filename)) {
                $filePath = 'uploads/documents/' . $filename;
                $this->userModel->addDocument($userId, $description, $filePath);
            }
        }

        if ($userId) {
            header('Location: ' . BASE_URL . '/users/edit/' . $userId);
        } else {
            header('Location: ' . BASE_URL . '/users');
        }
        exit;
    }

    public function deleteDocument($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $doc = $this->userModel->getDocumentById($id);
        if ($doc) {
            $fullPath = UPLOAD_DIR . 'documents/' . basename($doc['file_path']);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            $this->userModel->deleteDocument($id);
            header('Location: ' . BASE_URL . '/users/edit/' . $doc['user_id']);
            exit;
        }
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    // --- ACCIONES TRAYECTORIA ---

    public function addTrajectory() {
        header('Content-Type: application/json');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF Token']);
            exit;
        }

        $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $movement = $_POST['movement'] ?? null;
        $movementDate = $_POST['movement_date'] ?? null;
        $rehireable = $_POST['rehireable'] ?? 'Si';
        $notes = trim($_POST['notes'] ?? '');

        if ($userId && $movement && $movementDate) {
            $inserted = $this->userModel->addTrajectory($userId, $movement, $movementDate, $rehireable, $notes);
            echo json_encode(['success' => (bool)$inserted]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        }
        exit;
    }

    public function deleteTrajectory($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $tray = $this->userModel->getTrajectoryById($id);
        if ($tray) {
            $this->userModel->deleteTrajectory($id);
            header('Location: ' . BASE_URL . '/users/edit/' . $tray['user_id']);
            exit;
        }
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    // --- ACCIONES INFORMACIÓN ADICIONAL ---

    public function addAdditionalInfo() {
        header('Content-Type: application/json');

        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF Token']);
            exit;
        }

        $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $infoDate = $_POST['info_date'] ?? null;
        $description = trim($_POST['description'] ?? '');

        if ($userId && $infoDate && $description) {
            $inserted = $this->userModel->addAdditionalInfo($userId, $infoDate, $description);
            echo json_encode(['success' => (bool)$inserted]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        }
        exit;
    }

    public function uploadAdditionalInfoAttachment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $additionalInfoId = !empty($_POST['additional_info_id']) ? (int)$_POST['additional_info_id'] : null;
            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

            if ($additionalInfoId && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOAD_DIR . 'additionals/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $filename = 'add_' . $additionalInfoId . '_' . time() . '.' . strtolower($ext);

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                    $this->userModel->updateAdditionalInfoAttachment($additionalInfoId, $filename);
                }
            }

            if ($userId) {
                header('Location: ' . BASE_URL . '/users/edit/' . $userId);
            } else {
                header('Location: ' . BASE_URL . '/users');
            }
            exit;
        }
    }

    public function deleteAdditionalInfo($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $info = $this->userModel->getAdditionalInfoById($id);
        if ($info) {
            if (!empty($info['attachment_path'])) {
                $fullPath = UPLOAD_DIR . 'additionals/' . basename($info['attachment_path']);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $this->userModel->deleteAdditionalInfo($id);
            header('Location: ' . BASE_URL . '/users/edit/' . $info['user_id']);
            exit;
        }
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    // --- ACCIONES CAPACITACIONES DESDE EL PERFIL ---

    public function addTraining() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $trainingId = !empty($_POST['training_id']) ? (int)$_POST['training_id'] : null;

            if ($userId && $trainingId) {
                $this->trainingModel->assignToUser($userId, $trainingId);
            }

            if ($userId) {
                header('Location: ' . BASE_URL . '/users/edit/' . $userId);
            } else {
                header('Location: ' . BASE_URL . '/users');
            }
            exit;
        }
    }

    public function createAndAssignTraining() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $title = trim($_POST['title'] ?? '');
            $type = $_POST['type'] ?? 'Curso';
            $trainingDate = $_POST['training_date'] ?? date('Y-m-d');
            $durationHours = trim($_POST['duration_hours'] ?? '');

            if ($userId && !empty($title) && !empty($trainingDate)) {
                $data = [
                    'title' => $title,
                    'type' => $type,
                    'training_date' => $trainingDate,
                    'duration_hours' => $durationHours !== '' ? $durationHours : null
                ];

                $trainingId = $this->trainingModel->createWithUsers($data, [$userId]);

                if ($trainingId && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $userTrainings = $this->trainingModel->getByUser($userId);
                    if (!empty($userTrainings)) {
                        $lastUt = $userTrainings[0];
                        $userTrainingId = $lastUt['user_training_id'];

                        $uploadDir = UPLOAD_DIR . 'trainings/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                        $filename = 'cert_' . $userTrainingId . '_' . time() . '.' . strtolower($ext);

                        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                            $this->trainingModel->updateAttachment($userTrainingId, $filename);
                        }
                    }
                }
            }

            if ($userId) {
                header('Location: ' . BASE_URL . '/users/edit/' . $userId);
            } else {
                header('Location: ' . BASE_URL . '/users');
            }
            exit;
        }
    }

    public function uploadTrainingAttachment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $userTrainingId = !empty($_POST['user_training_id']) ? (int)$_POST['user_training_id'] : null;
            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

            if ($userTrainingId && isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOAD_DIR . 'trainings/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $filename = 'cert_' . $userTrainingId . '_' . time() . '.' . strtolower($ext);

                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                    $this->trainingModel->updateAttachment($userTrainingId, $filename);
                }
            }

            if ($userId) {
                header('Location: ' . BASE_URL . '/users/edit/' . $userId);
            } else {
                header('Location: ' . BASE_URL . '/users');
            }
            exit;
        }
    }

    public function deleteUserTraining() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $userTrainingId = !empty($_POST['user_training_id']) ? (int)$_POST['user_training_id'] : null;
            $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

            if ($userTrainingId) {
                $this->trainingModel->removeUserTraining($userTrainingId);
            }

            if ($userId) {
                header('Location: ' . BASE_URL . '/users/edit/' . $userId);
            } else {
                header('Location: ' . BASE_URL . '/users');
            }
            exit;
        }
    }
}
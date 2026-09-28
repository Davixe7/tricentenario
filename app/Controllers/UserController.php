<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Area.php';
require_once __DIR__ . '/../Models/Position.php';

class UserController {
    private $userModel;
    private $areaModel;
    private $positionModel;

    public function __construct() {
        Auth::check();
        $this->userModel = new User();
        $this->areaModel = new Area();
        $this->positionModel = new Position();
    }

    public function index() {
        $statusFilter = $_GET['status'] ?? 'active';
        $users = $this->userModel->getAll($statusFilter);
        require_once __DIR__ . '/../Views/users/index.php';
    }

    public function create() {
        $user = null;
        $areas = $this->areaModel->getAll('active');
        $positions = $this->positionModel->getAll('active');
        $activeUsers = $this->userModel->getAll('active');
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
            'employee_number' => (int)$_POST['employee_number'],
            'first_name' => trim($_POST['first_name']),
            'last_name_paternal' => trim($_POST['last_name_paternal']),
            'last_name_maternal' => trim($_POST['last_name_maternal'] ?? ''),
            'username' => trim($_POST['username']),
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'],
            'area_id' => (int)$_POST['area_id'],
            'position_id' => (int)$_POST['position_id'],
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
        $activeUsers = $this->userModel->getAll('active');
        require_once __DIR__ . '/../Views/users/form.php';
    }

    public function update($id) {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $existingUser = $this->userModel->find($id);

        $data = [
            'employee_number' => (int)$_POST['employee_number'],
            'first_name' => trim($_POST['first_name']),
            'last_name_paternal' => trim($_POST['last_name_paternal']),
            'last_name_maternal' => trim($_POST['last_name_maternal'] ?? ''),
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $_POST['role'],
            'area_id' => (int)$_POST['area_id'],
            'position_id' => (int)$_POST['position_id'],
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
}
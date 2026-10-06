<?php
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/users');
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function login() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = "User account is inactive";
                require_once __DIR__ . '/../Views/auth/login.php';
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id'                 => $user['id'],
                'name'               => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name_paternal'] ?? '')),
                'first_name'         => $user['first_name'] ?? '',
                'last_name_paternal' => $user['last_name_paternal'] ?? '',
                'username'           => $user['username'] ?? '',
                'role'               => $user['role'] ?? '',
                'avatar'             => $user['avatar'] ?? null
            ];

            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        $error = "Invalid username or password";
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validaciones básicas
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = "Todos los campos son obligatorios.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = "La nueva contraseña y la confirmación no coinciden.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
            exit;
        }

        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        // Verificar la contraseña actual
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash_error'] = "La contraseña actual es incorrecta.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
            exit;
        }

        // Encriptar y actualizar
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel->update($_SESSION['user_id'], ['password' => $hashedPassword]);

        $_SESSION['flash_success'] = "Contraseña actualizada exitosamente.";
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
        exit;
    }

    public function logout() {
        Auth::init();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
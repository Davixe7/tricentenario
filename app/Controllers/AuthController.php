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
                'id' => $user['id'],
                'name' => $user['first_name'] . ' ' . $user['last_name_paternal'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        $error = "Invalid username or password";
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function logout() {
        Auth::init();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
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

        $error = "Usuario o contraseña incorrectos";
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    // --- RECUPERAR CONTRASEÑA ---

    public function showForgotPassword() {
        require_once __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function forgotPassword() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }

        $identifier = trim($_POST['identifier'] ?? '');
        $userModel = new User();
        $user = $userModel->findByEmailOrUsername($identifier);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $userModel->saveResetToken($user['id'], $token, $expires);

            $resetUrl = BASE_URL . '/reset-password?token=' . $token;

            // En entorno de desarrollo mostramos enlace directo.
            // Para producción se envía vía función mail() o PHPMailer.
            $_SESSION['flash_info'] = "Enlace generado para restablecer la contraseña (copiar para pruebas en desarrollo):<br><a href='{$resetUrl}' class='fw-bold text-dark'>{$resetUrl}</a>";
        } else {
            $_SESSION['flash_info'] = "Si el usuario o correo existe en el sistema, se ha generado la instrucción de recuperación.";
        }

        header('Location: ' . BASE_URL . '/forgot-password');
        exit;
    }

    public function showResetPassword() {
        $token = $_GET['token'] ?? '';
        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            $error = "El enlace de recuperación es inválido o ha expirado.";
        }

        require_once __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function resetPassword() {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }

        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByResetToken($token);

        if (!$user) {
            $error = "El enlace de recuperación es inválido o ha expirado.";
            require_once __DIR__ . '/../Views/auth/reset_password.php';
            return;
        }

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
            require_once __DIR__ . '/../Views/auth/reset_password.php';
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $error = "Las contraseñas no coinciden.";
            require_once __DIR__ . '/../Views/auth/reset_password.php';
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $userModel->updatePasswordAndClearToken($user['id'], $hashedPassword);

        $_SESSION['flash_success'] = "Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión.";
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

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

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash_error'] = "La contraseña actual es incorrecta.";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/users'));
            exit;
        }

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
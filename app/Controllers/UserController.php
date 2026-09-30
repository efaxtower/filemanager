<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\UserRepository;

final class UserController
{
    private Auth $auth;
    private UserRepository $users;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->users = new UserRepository();
    }

    /**
     * Muestra el formulario de perfil (cambiar contraseña).
     */
    public function profile(): void
    {
        $user = $this->auth->requireLogin();
        require __DIR__ . '/../Views/user/profile.php';
    }

    /**
     * Procesa el cambio de contraseña.
     */
    public function updatePassword(): void
    {
        $user = $this->auth->requireLogin();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta';
            header('Location: /filemanager/profile');
            exit;
        }

        if (strlen($new) < 8) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 8 caracteres';
            header('Location: /filemanager/profile');
            exit;
        }

        if ($new !== $confirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: /filemanager/profile');
            exit;
        }

        try {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $this->users->updatePassword((int) $user['id'], $hash);
            $_SESSION['success'] = 'Contraseña actualizada';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/profile');
        exit;
    }
}
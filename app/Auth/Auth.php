<?php

namespace App\Auth;

final class Auth
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * Registra un usuario. Devuelve el id del nuevo usuario.
     * Lanza excepción si el username es inválido o ya existe.
     */
    public function register(string $username, string $password): int
    {
        // 1. Validar formato del username
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            throw new \InvalidArgumentException(
                'El usuario solo puede contener letras sin tildes, números y guion bajo (3-50 caracteres)'
            );
        }

        // 2. Validar longitud de contraseña
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException(
                'La contraseña debe tener al menos 8 caracteres'
            );
        }

        // 3. Verificar que no exista
        if ($this->users->exists($username)) {
            throw new \InvalidArgumentException('El usuario ya existe');
        }

        // 4. Hash de la contraseña
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // 5. Crear el usuario
        return $this->users->create($username, $hash);
    }

    /**
     * Verifica credenciales. Si son válidas, inicia sesión.
     * Devuelve true si el login fue exitoso.
     */
    public function login(string $username, string $password): bool
    {
        $user = $this->users->findByUsername($username);

        if ($user === null) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Credenciales válidas → iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = (int) $user['id'];

        return true;
    }

    /**
     * Cierra la sesión del usuario actual.
     * Si no hay sesión activa, no hace nada.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
    }

    /**
     * Devuelve el usuario logueado, o null si no hay sesión.
     * No inicia sesión. Solo lee la que ya existe.
     */
    public function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return $this->users->findById((int) $_SESSION['user_id']);
        }
        return null;
    }

    /**
     * ¿Hay usuario logueado?
     */
    public function check(): bool
    {
        return $this->currentUser() !== null;
    }
        /**
     * ¿El usuario actual es admin?
     */
    public function isAdmin(): bool
    {
        $user = $this->currentUser();
        return $user !== null && $user['role'] === 'admin';
    }

    /**
     * Verifica que el usuario sea admin. Si no, redirige.
     * Devuelve el usuario si es admin.
     */
    public function requireAdmin(): array
    {
        $user = $this->currentUser();
        if ($user === null) {
            header('Location: /filemanager/login');
            exit;
        }
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo '403 - Acceso denegado';
            exit;
        }
        return $user;
    }
        /**
     * Verifica que haya sesión. Si no, redirige a /login.
     */
    public function requireLogin(): array
    {
        $user = $this->currentUser();
        if ($user === null) {
            header('Location: /filemanager/login');
            exit;
        }
        return $user;
    }
}
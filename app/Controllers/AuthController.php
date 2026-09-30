<?php

namespace App\Controllers;

use App\Auth\Auth;

final class AuthController
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * Muestra el formulario de login.
     */
    public function showLogin(): void
    {
        if ($this->auth->check()) {
            header('Location: /filemanager/');
            exit;
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Procesa el POST del login.
     */
    public function processLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['error'] = 'Usuario y contraseña son obligatorios';
            header('Location: /filemanager/login');
            exit;
        }

        if ($this->auth->login($username, $password)) {
            header('Location: /filemanager/');
            exit;
        }

        $_SESSION['error'] = 'Usuario o contraseña incorrectos';
        header('Location: /filemanager/login');
        exit;
    }

    /**
     * Muestra el formulario de registro.
     */
    public function showRegister(): void
    {
        if ($this->auth->check()) {
            header('Location: /filemanager/');
            exit;
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        require __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Procesa el POST del registro.
     */
    public function processRegister(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $this->auth->register($username, $password);
            $_SESSION['success'] = 'Usuario registrado. Ahora puedes iniciar sesión.';
            header('Location: /filemanager/login');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /filemanager/register');
            exit;
        }
    }

    /**
     * Cierra sesión y redirige al login.
     */
    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /filemanager/login');
        exit;
    }
}
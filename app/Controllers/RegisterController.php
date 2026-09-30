<?php

namespace App\Controllers;

use App\Auth\UserRepository;
use App\Core\AccountRequestRepository;
use App\Core\Captcha;
use App\Core\DepartmentRepository;

final class RegisterController
{
    private UserRepository $users;
    private AccountRequestRepository $requests;
    private DepartmentRepository $departments;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->requests = new AccountRequestRepository();
        $this->departments = new DepartmentRepository();
    }

    /**
     * Muestra el formulario de solicitud de cuenta.
     */
    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $departments = $this->departments->findAll();
        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        require __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Procesa la solicitud.
     */
    public function submit(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $departmentId = $_POST['department_id'] ?? '';
        $message = trim($_POST['message'] ?? '');
        $captchaInput = strtoupper(trim($_POST['captcha'] ?? ''));

        // Validar captcha
        $captchaExpected = $_SESSION['captcha_code'] ?? '';
        unset($_SESSION['captcha_code']);

        if ($captchaExpected === '' || $captchaInput !== strtoupper($captchaExpected)) {
            $_SESSION['error'] = 'Captcha incorrecto';
            header('Location: /filemanager/register');
            exit;
        }

        // Validar username
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $_SESSION['error'] = 'Usuario inválido (solo letras sin tildes, números y guion bajo, 3-50 caracteres)';
            header('Location: /filemanager/register');
            exit;
        }

        // Verificar que no exista ya un usuario con ese nombre
        if ($this->users->exists($username)) {
            $_SESSION['error'] = 'Ya existe un usuario con ese nombre';
            header('Location: /filemanager/register');
            exit;
        }

        // Verificar que no haya una solicitud pendiente con ese nombre
        if ($this->requests->existsPending($username)) {
            $_SESSION['error'] = 'Ya hay una solicitud pendiente con ese nombre';
            header('Location: /filemanager/register');
            exit;
        }

        $deptId = ($departmentId === '' || $departmentId === '0') ? null : (int) $departmentId;

        try {
            $this->requests->create($username, $deptId, $message ?: null);
            $_SESSION['success'] = 'Solicitud enviada. El administrador la revisará pronto.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error al enviar la solicitud: ' . $e->getMessage();
        }

        header('Location: /filemanager/register');
        exit;
    }

    /**
     * Genera y devuelve la imagen del captcha.
     */
    public function captcha(): void
    {
        // DEBUG
        $log = __DIR__ . '/../../storage/captcha_debug.log';
        file_put_contents($log, "=== " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
        file_put_contents($log, "1. Método captcha() llamado\n", FILE_APPEND);
        file_put_contents($log, "2. GD cargado: " . (extension_loaded('gd') ? 'SÍ' : 'NO') . "\n", FILE_APPEND);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        file_put_contents($log, "3. Sesión iniciada. Status: " . session_status() . "\n", FILE_APPEND);

        try {
            $code = Captcha::generateCode();
            file_put_contents($log, "4. Código generado: $code\n", FILE_APPEND);

            $_SESSION['captcha_code'] = $code;
            file_put_contents($log, "5. Código guardado en sesión\n", FILE_APPEND);

            $binary = Captcha::generateImage($code);
            file_put_contents($log, "6. Imagen generada. Tamaño: " . strlen($binary) . " bytes\n", FILE_APPEND);
        } catch (\Throwable $e) {
            file_put_contents($log, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($log, "Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            http_response_code(500);
            echo "Error generando captcha: " . $e->getMessage();
            exit;
        }

        // Verificar si headers ya fueron enviados
        if (headers_sent($file, $line)) {
            file_put_contents($log, "AVISO: Headers ya enviados en $file:$line\n", FILE_APPEND);
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        file_put_contents($log, "7. Headers enviados. Output buffer level: " . ob_get_level() . "\n", FILE_APPEND);

        echo $binary;
        file_put_contents($log, "8. Imagen enviada al navegador\n", FILE_APPEND);
        exit;
    }
}
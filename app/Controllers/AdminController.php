<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\UserRepository;
use App\Core\DepartmentRepository;
use App\Core\AccountRequestRepository;
use App\Core\ReportRepository;
use App\Core\SharedRepository;

final class AdminController
{
    private Auth $auth;
    private UserRepository $users;
    private DepartmentRepository $departments;
    private AccountRequestRepository $requests;
    private ReportRepository $reports;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->users = new UserRepository();
        $this->departments = new DepartmentRepository();
        $this->requests = new AccountRequestRepository();
        $this->reports = new ReportRepository();
        $this->shared = new SharedRepository();
    }

    /**
     * Lista de usuarios.
     */
    public function users(): void
    {
        $admin = $this->auth->requireAdmin();
        $users = $this->users->findAll();
        $departments = $this->departments->findAll();

        require __DIR__ . '/../Views/admin/users.php';
    }

    /**
     * Actualiza rol y departamento de un usuario.
     */
    public function updateUser(): void
    {
        $this->auth->requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        $departmentId = $_POST['department_id'] ?? '';

        if ($userId <= 0) {
            $_SESSION['error'] = 'Usuario inválido';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if (!in_array($role, ['admin', 'user'], true)) {
            $_SESSION['error'] = 'Rol inválido';
            header('Location: /filemanager/admin/users');
            exit;
        }

        $currentAdmin = $this->auth->currentUser();
        if ($userId === (int) $currentAdmin['id'] && $role !== 'admin') {
            $_SESSION['error'] = 'No puedes quitarte tu propio rol de admin';
            header('Location: /filemanager/admin/users');
            exit;
        }

        $deptId = ($departmentId === '' || $departmentId === '0') ? null : (int) $departmentId;

        try {
            $this->users->updateRole($userId, $role);
            $this->users->updateDepartment($userId, $deptId);
            $_SESSION['success'] = 'Usuario actualizado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/users');
        exit;
    }

    /**
     * Crea un usuario nuevo.
     */
    public function createUser(): void
    {
        $this->auth->requireAdmin();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $departmentId = $_POST['department_id'] ?? '';
        $quotaGb = (int) ($_POST['quota_gb'] ?? 15);

        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $_SESSION['error'] = 'Usuario inválido (solo letras sin tildes, números y guion bajo, 3-50 caracteres)';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if (!in_array($role, ['admin', 'user'], true)) {
            $_SESSION['error'] = 'Rol inválido';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if ($this->users->exists($username)) {
            $_SESSION['error'] = 'Ya existe un usuario con ese nombre';
            header('Location: /filemanager/admin/users');
            exit;
        }

        $deptId = ($departmentId === '' || $departmentId === '0') ? null : (int) $departmentId;
        $quotaBytes = max(1, $quotaGb) * 1024 * 1024 * 1024;

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $userId = $this->users->create($username, $hash, $role);

            if ($deptId !== null) {
                $this->users->updateDepartment($userId, $deptId);
            }
            $this->users->updateQuota($userId, $quotaBytes);

            $config = require __DIR__ . '/../../config/config.php';
            $userDir = $config['storage']['path'] . '/users/' . $userId;
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            $_SESSION['success'] = "Usuario '$username' creado. Contraseña: $password";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/users');
        exit;
    }

    /**
     * Resetea la contraseña de un usuario.
     */
    public function resetPassword(): void
    {
        $this->auth->requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($userId <= 0) {
            $_SESSION['error'] = 'Usuario inválido';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
            header('Location: /filemanager/admin/users');
            exit;
        }

        $target = $this->users->findById($userId);
        if ($target === null) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: /filemanager/admin/users');
            exit;
        }

        try {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->users->updatePassword($userId, $hash);
            $_SESSION['success'] = "Contraseña de '{$target['username']}' actualizada a: $newPassword";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/users');
        exit;
    }

    /**
     * Elimina un usuario.
     */
    public function deleteUser(): void
    {
        $admin = $this->auth->requireAdmin();

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['error'] = 'Usuario inválido';
            header('Location: /filemanager/admin/users');
            exit;
        }

        if ($userId === (int) $admin['id']) {
            $_SESSION['error'] = 'No puedes eliminar tu propia cuenta';
            header('Location: /filemanager/admin/users');
            exit;
        }

        $target = $this->users->findById($userId);
        if ($target === null) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header('Location: /filemanager/admin/users');
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $userDir = $config['storage']['path'] . '/users/' . $userId;
            if (is_dir($userDir)) {
                $this->deleteDirectory($userDir);
            }

            $this->users->delete($userId);

            $_SESSION['success'] = "Usuario '{$target['username']}' eliminado";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/users');
        exit;
    }

    /**
     * Lista de departamentos.
     */
    public function departments(): void
    {
        $admin = $this->auth->requireAdmin();
        $departments = $this->departments->findAll();

        foreach ($departments as &$dept) {
            $dept['user_count'] = $this->departments->countUsers((int) $dept['id']);
        }
        unset($dept);

        require __DIR__ . '/../Views/admin/departments.php';
    }

    /**
     * Crea un departamento.
     */
    public function createDepartment(): void
    {
        $this->auth->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = 'El nombre es obligatorio';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        if ($this->departments->exists($name)) {
            $_SESSION['error'] = 'Ya existe un departamento con ese nombre';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        try {
            $this->departments->create($name, $description ?: null);
            $_SESSION['success'] = 'Departamento creado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/departments');
        exit;
    }

    /**
     * Elimina un departamento.
     */
    public function deleteDepartment(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Departamento inválido';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        $count = $this->departments->countUsers($id);
        if ($count > 0) {
            $_SESSION['error'] = "No se puede borrar: hay $count usuario(s) asignados";
            header('Location: /filemanager/admin/departments');
            exit;
        }

        try {
            $this->departments->delete($id);
            $_SESSION['success'] = 'Departamento eliminado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/departments');
        exit;
    }

    /**
     * Devuelve los datos de un departamento (JSON).
     */
    public function editDepartment(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        $dept = $this->departments->findById($id);

        header('Content-Type: application/json');
        if ($dept === null) {
            echo json_encode(['error' => 'No encontrado']);
            exit;
        }

        echo json_encode([
            'id' => (int) $dept['id'],
            'name' => $dept['name'],
            'description' => $dept['description'] ?? '',
        ]);
        exit;
    }

    /**
     * Actualiza un departamento.
     */
    public function updateDepartment(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = 'Departamento inválido';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        if ($name === '') {
            $_SESSION['error'] = 'El nombre es obligatorio';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        $existing = $this->departments->findById($id);
        if ($existing === null) {
            $_SESSION['error'] = 'Departamento no encontrado';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        if ($existing['name'] !== $name && $this->departments->exists($name)) {
            $_SESSION['error'] = 'Ya existe otro departamento con ese nombre';
            header('Location: /filemanager/admin/departments');
            exit;
        }

        try {
            $this->departments->update($id, $name, $description ?: null);
            $_SESSION['success'] = 'Departamento actualizado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/departments');
        exit;
    }

    /**
     * Lista de solicitudes de cuenta.
     */
    public function requests(): void
    {
        $this->auth->requireAdmin();

        $status = $_GET['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'approved', 'rejected', 'cancelled', 'all'], true)) {
            $status = 'pending';
        }

        $requests = $this->requests->findAll($status === 'all' ? null : $status);
        $pendingCount = $this->requests->countPending();
        $departments = $this->departments->findAll();

        require __DIR__ . '/../Views/admin/requests.php';
    }

    /**
     * Aprueba una solicitud → crea el usuario.
     */
    public function approveRequest(): void
    {
        $admin = $this->auth->requireAdmin();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if ($requestId <= 0) {
            $_SESSION['error'] = 'Solicitud inválida';
            header('Location: /filemanager/admin/requests');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres';
            header('Location: /filemanager/admin/requests');
            exit;
        }

        $request = $this->requests->findById($requestId);
        if ($request === null || $request['status'] !== 'pending') {
            $_SESSION['error'] = 'Solicitud no encontrada o ya procesada';
            header('Location: /filemanager/admin/requests');
            exit;
        }

        if ($this->users->exists($request['username'])) {
            $_SESSION['error'] = 'El usuario "' . $request['username'] . '" ya existe. Rechaza la solicitud.';
            header('Location: /filemanager/admin/requests');
            exit;
        }

        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $userId = $this->users->create($request['username'], $hash, 'user');

            if (!empty($request['department_id'])) {
                $this->users->updateDepartment($userId, (int) $request['department_id']);
            }

            $config = require __DIR__ . '/../../config/config.php';
            $userDir = $config['storage']['path'] . '/users/' . $userId;
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            $this->requests->approve($requestId, (int) $admin['id']);

            $_SESSION['success'] = "Usuario '{$request['username']}' creado con contraseña: $password";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/requests');
        exit;
    }

    /**
     * Rechaza una solicitud.
     */
    public function rejectRequest(): void
    {
        $admin = $this->auth->requireAdmin();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($requestId <= 0) {
            $_SESSION['error'] = 'Solicitud inválida';
            header('Location: /filemanager/admin/requests');
            exit;
        }

        if ($reason === '') {
            $reason = 'Sin motivo especificado';
        }

        try {
            $this->requests->reject($requestId, (int) $admin['id'], $reason);
            $_SESSION['success'] = 'Solicitud rechazada';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/requests');
        exit;
    }

    /**
     * Borra una carpeta recursivamente.
     */
        /**
     * Lista de todos los reportes (admin).
     */
    public function reports(): void
    {
        $this->auth->requireAdmin();

        $status = $_GET['status'] ?? 'all';
        $priority = $_GET['priority'] ?? 'all';

        if (!in_array($status, ['all', 'pending', 'in_review', 'resolved', 'closed'], true)) {
            $status = 'all';
        }
        if (!in_array($priority, ['all', 'low', 'medium', 'high'], true)) {
            $priority = 'all';
        }

        $reports = $this->reports->findAll(
            $status === 'all' ? null : $status,
            $priority === 'all' ? null : $priority
        );
        $pendingCount = $this->reports->countPending();

        require __DIR__ . '/../Views/admin/reports.php';
    }

    /**
     * Ver un reporte desde el panel de admin.
     */
    public function viewReport(int $id): void
    {
        $admin = $this->auth->requireAdmin();

        $report = $this->reports->findById($id);
        if ($report === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        $isAdmin = true;
        require __DIR__ . '/../Views/admin/report_view.php';
    }

    /**
     * Responde un reporte.
     */
    public function respondReport(): void
    {
        $admin = $this->auth->requireAdmin();

        $id = (int) ($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? 'in_review';
        $response = trim($_POST['admin_response'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = 'Reporte inválido';
            header('Location: /filemanager/admin/reports');
            exit;
        }

        if (!in_array($status, ['pending', 'in_review', 'resolved', 'closed'], true)) {
            $status = 'in_review';
        }

        try {
            $this->reports->respond($id, (int) $admin['id'], $status, $response ?: null);
            $_SESSION['success'] = 'Reporte actualizado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/reports/view/' . $id);
        exit;
    }

    /**
     * Lista de carpetas compartidas (admin).
     */
    public function shared(): void
    {
        $this->auth->requireAdmin();

        $folders = $this->shared->findChildren(null);
        $departments = $this->departments->findAll();

        require __DIR__ . '/../Views/admin/shared.php';
    }

    /**
     * Crea una carpeta compartida raíz.
     */
    public function createSharedFolder(): void
    {
        $admin = $this->auth->requireAdmin();

        $name = trim($_POST['name'] ?? '');
        $departmentId = $_POST['department_id'] ?? '';
        $isPublic = isset($_POST['is_public']);

        if ($name === '' || !preg_match('/^[^\/\\\\:*?"<>|]+$/', $name)) {
            $_SESSION['error'] = 'Nombre de carpeta inválido';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        if ($this->shared->folderExists(null, $name)) {
            $_SESSION['error'] = 'Ya existe una carpeta con ese nombre';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        $deptId = ($departmentId === '' || $departmentId === '0') ? null : (int) $departmentId;

        try {
            $this->shared->createFolder($name, null, (int) $admin['id'], $deptId, $isPublic);

            // Crear carpeta física
            $config = require __DIR__ . '/../../config/config.php';
            $sharedDir = $config['storage']['path'] . '/shared';
            if (!is_dir($sharedDir)) {
                mkdir($sharedDir, 0755, true);
            }

            $_SESSION['success'] = "Carpeta compartida '$name' creada";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/shared');
        exit;
    }

    /**
     * Ver una carpeta compartida y gestionar permisos.
     */
    public function viewSharedFolder(int $id): void
    {
        $this->auth->requireAdmin();

        $folder = $this->shared->findFolderById($id);
        if ($folder === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        $permissions = $this->shared->findPermissions($id);
        $children = $this->shared->findChildren($id);
        $allUsers = $this->users->findAll();
        $departments = $this->departments->findAll();

        require __DIR__ . '/../Views/admin/shared_view.php';
    }

    /**
     * Actualiza meta de una carpeta compartida (departamento y visibilidad).
     */
    public function updateSharedFolder(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_POST['folder_id'] ?? 0);
        $departmentId = $_POST['department_id'] ?? '';
        $isPublic = isset($_POST['is_public']);

        if ($id <= 0) {
            $_SESSION['error'] = 'Carpeta inválida';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        $deptId = ($departmentId === '' || $departmentId === '0') ? null : (int) $departmentId;

        try {
            $this->shared->updateFolderMeta($id, $deptId, $isPublic);
            $_SESSION['success'] = 'Carpeta actualizada';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/shared/view/' . $id);
        exit;
    }

    /**
     * Añade o actualiza un permiso individual.
     */
    public function setSharedPermission(): void
    {
        $this->auth->requireAdmin();

        $folderId = (int) ($_POST['folder_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);
        $canRead = isset($_POST['can_read']);
        $canWrite = isset($_POST['can_write']);
        $canDelete = isset($_POST['can_delete']);

        if ($folderId <= 0 || $userId <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        try {
            $this->shared->setPermission($folderId, $userId, $canRead, $canWrite, $canDelete);
            $_SESSION['success'] = 'Permiso actualizado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/shared/view/' . $folderId);
        exit;
    }

    /**
     * Elimina un permiso individual.
     */
    public function deleteSharedPermission(): void
    {
        $this->auth->requireAdmin();

        $folderId = (int) ($_POST['folder_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($folderId <= 0 || $userId <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        try {
            $this->shared->deletePermission($folderId, $userId);
            $_SESSION['success'] = 'Permiso eliminado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/shared/view/' . $folderId);
        exit;
    }

    /**
     * Borra una carpeta compartida.
     */
    public function deleteSharedFolder(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_POST['folder_id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Carpeta inválida';
            header('Location: /filemanager/admin/shared');
            exit;
        }

        try {
            $folder = $this->shared->findFolderById($id);
            if ($folder !== null) {
                $config = require __DIR__ . '/../../config/config.php';
                $path = $config['storage']['path'] . '/shared/' . $folder['name'];
                if (is_dir($path)) {
                    $this->deleteDirectory($path);
                }
            }

            $this->shared->deleteFolder($id);
            $_SESSION['success'] = 'Carpeta eliminada';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/admin/shared');
        exit;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
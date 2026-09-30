<?php

session_start();

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Core/DepartmentRepository.php';
require __DIR__ . '/../app/Core/AccountRequestRepository.php';
require __DIR__ . '/../app/Core/ReportRepository.php';
require __DIR__ . '/../app/Core/Captcha.php';
require __DIR__ . '/../app/Core/SharedRepository.php';
require __DIR__ . '/../app/Auth/UserRepository.php';
require __DIR__ . '/../app/Auth/Auth.php';
require __DIR__ . '/../app/Filesystem/PathNotFoundException.php';
require __DIR__ . '/../app/Filesystem/ResolvedPath.php';
require __DIR__ . '/../app/Filesystem/PathResolver.php';
require __DIR__ . '/../app/Filesystem/NodeRepository.php';
require __DIR__ . '/../app/Filesystem/Storage.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/FileController.php';
require __DIR__ . '/../app/Controllers/AdminController.php';
require __DIR__ . '/../app/Controllers/UserController.php';
require __DIR__ . '/../app/Controllers/RegisterController.php';
require __DIR__ . '/../app/Controllers/ReportController.php';
require __DIR__ . '/../app/Controllers/SharedController.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\FileController;
use App\Controllers\AdminController;
use App\Controllers\UserController;
use App\Controllers\RegisterController;
use App\Controllers\ReportController;
use App\Controllers\SharedController;

$uri = $_SERVER['REQUEST_URI'] ?? '/';

$basePath = '/filemanager';
if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

// Quitar /public/ si Apache lo añadió por la redirección
if (str_starts_with($uri, '/public/')) {
    $uri = substr($uri, strlen('/public'));
}
if ($uri === '/public') {
    $uri = '/';
}

if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

$uri = urldecode($uri);

if ($uri === '' || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// ============================================
// INSTANCIAS
// ============================================
$router = new Router();
$auth = new AuthController();
$files = new FileController();
$admin = new AdminController();
$userCtrl = new UserController();
$register = new RegisterController();
$reportCtrl = new ReportController();
$sharedCtrl = new SharedController();

// ============================================
// AUTH
// ============================================
$router->get('/login', [$auth, 'showLogin']);
$router->post('/login', [$auth, 'processLogin']);
$router->get('/logout', [$auth, 'logout']);

// ============================================
// REGISTRO PÚBLICO (SOLICITUD DE CUENTA)
// ============================================
$router->get('/register', [$register, 'show']);
$router->post('/register', [$register, 'submit']);

// ============================================
// FILES (POST)
// ============================================
$router->post('/files/folder', [$files, 'createFolder']);
$router->post('/files/upload', [$files, 'upload']);
$router->post('/files/rename', [$files, 'rename']);
$router->post('/files/delete', [$files, 'delete']);

// ============================================
// ADMIN (POST)
// ============================================
$router->post('/admin/users/create', [$admin, 'createUser']);
$router->post('/admin/users/update', [$admin, 'updateUser']);
$router->post('/admin/users/delete', [$admin, 'deleteUser']);
$router->post('/admin/users/reset-password', [$admin, 'resetPassword']);
$router->post('/admin/departments/create', [$admin, 'createDepartment']);
$router->post('/admin/departments/delete', [$admin, 'deleteDepartment']);
$router->post('/admin/departments/update', [$admin, 'updateDepartment']);
$router->post('/admin/requests/approve', [$admin, 'approveRequest']);
$router->post('/admin/requests/reject', [$admin, 'rejectRequest']);
$router->post('/admin/reports/respond', [$admin, 'respondReport']);
$router->post('/admin/shared/create', [$admin, 'createSharedFolder']);
$router->post('/admin/shared/update', [$admin, 'updateSharedFolder']);
$router->post('/admin/shared/delete', [$admin, 'deleteSharedFolder']);
$router->post('/admin/shared/permission/set', [$admin, 'setSharedPermission']);
$router->post('/admin/shared/permission/delete', [$admin, 'deleteSharedPermission']);

// ============================================
// REPORTS (POST)
// ============================================
$router->post('/reports/create', [$reportCtrl, 'create']);

// ============================================
// SHARED (POST)
// ============================================
$router->post('/shared/folder/create', [$sharedCtrl, 'createFolder']);
$router->post('/shared/upload', [$sharedCtrl, 'upload']);
$router->post('/shared/rename-file', [$sharedCtrl, 'renameFile']);
$router->post('/shared/delete-file', [$sharedCtrl, 'deleteFile']);
$router->post('/shared/delete-folder', [$sharedCtrl, 'deleteFolder']);
$router->post('/shared/save-file', [$sharedCtrl, 'saveFile']);
$router->post('/shared/replace-file', [$sharedCtrl, 'replaceFile']);
$router->post('/shared/move-file', [$sharedCtrl, 'moveFile']);
$router->post('/shared/move-folder', [$sharedCtrl, 'moveFolder']);

// ============================================
// PROFILE (POST)
// ============================================
$router->post('/profile/password', [$userCtrl, 'updatePassword']);

// ============================================
// RUTAS GET EXACTAS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($uri === '/captcha') { $register->captcha(); exit; }
    if ($uri === '/admin/users') { $admin->users(); exit; }
    if ($uri === '/admin/departments') { $admin->departments(); exit; }
    if ($uri === '/admin/requests') { $admin->requests(); exit; }
    if ($uri === '/admin/reports') { $admin->reports(); exit; }
    if ($uri === '/admin/shared') { $admin->shared(); exit; }
    if (str_starts_with($uri, '/admin/departments/edit')) { $admin->editDepartment(); exit; }
    if ($uri === '/profile') { $userCtrl->profile(); exit; }
    if ($uri === '/reports') { $reportCtrl->index(); exit; }
    if ($uri === '/reports/create') { $reportCtrl->showCreate(); exit; }
    if ($uri === '/shared') { $sharedCtrl->index(); exit; }
    if ($uri === '/shared/target-folders') { $sharedCtrl->listTargetFolders(); exit; }
}

// ============================================
// RUTAS DINÁMICAS GET
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Shared: ver archivo
    if (str_starts_with($uri, '/shared/view-file/')) {
        $id = (int) substr($uri, strlen('/shared/view-file/'));
        $sharedCtrl->viewFile($id);
        exit;
    }

    // Shared: descargar archivo
    if (str_starts_with($uri, '/shared/download/')) {
        $id = (int) substr($uri, strlen('/shared/download/'));
        $sharedCtrl->download($id);
        exit;
    }

    // Shared: ver carpeta (usuario)
    if (str_starts_with($uri, '/shared/view/')) {
        $id = (int) substr($uri, strlen('/shared/view/'));
        $sharedCtrl->view($id);
        exit;
    }

    // Shared: ver carpeta (admin)
    if (str_starts_with($uri, '/admin/shared/view/')) {
        $id = (int) substr($uri, strlen('/admin/shared/view/'));
        $admin->viewSharedFolder($id);
        exit;
    }

    // Admin: ver reporte
    if (str_starts_with($uri, '/admin/reports/view/')) {
        $id = (int) substr($uri, strlen('/admin/reports/view/'));
        $admin->viewReport($id);
        exit;
    }

    // Usuario: ver reporte
    if (str_starts_with($uri, '/reports/view/')) {
        $id = (int) substr($uri, strlen('/reports/view/'));
        $reportCtrl->view($id);
        exit;
    }

    if (str_starts_with($uri, '/search')) {
        $files->search();
        exit;
    }

    if (str_starts_with($uri, '/files/view')) {
        $path = substr($uri, strlen('/files/view')) ?: '/';
        $files->view($path);
        exit;
    }

    if (str_starts_with($uri, '/files/download')) {
        $path = substr($uri, strlen('/files/download')) ?: '/';
        $files->download($path);
        exit;
    }

    if ($uri === '/' || $uri === '') {
        $files->index('/');
        exit;
    }

    if (str_starts_with($uri, '/files')) {
        $path = substr($uri, strlen('/files')) ?: '/';
        $files->index($path);
        exit;
    }
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
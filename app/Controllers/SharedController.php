<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\SharedRepository;

final class SharedController
{
    private Auth $auth;
    private SharedRepository $shared;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->shared = new SharedRepository();
    }

    /**
     * Explorador de carpetas compartidas (raíz).
     */
    public function index(): void
    {
        $user = $this->auth->requireLogin();

        $folders = $this->shared->findChildrenForUser(
            null,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin'
        );

        require __DIR__ . '/../Views/shared/index.php';
    }

    /**
     * Ver contenido de una carpeta compartida.
     */
    public function view(int $folderId): void
    {
        $user = $this->auth->requireLogin();

        $folder = $this->shared->findFolderById($folderId);
        if ($folder === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        $canAccess = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'read'
        );

        if (!$canAccess) {
            http_response_code(403);
            echo '403 - No tienes acceso a esta carpeta';
            exit;
        }

        $children = $this->shared->findChildrenForUser(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin'
        );

        $files = $this->shared->findFiles($folderId);

        $canWrite = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        require __DIR__ . '/../Views/shared/view.php';
    }

    /**
     * Crea una subcarpeta dentro de una carpeta compartida.
     */
    public function createFolder(): void
    {
        $user = $this->auth->requireLogin();

        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($parentId <= 0 || $name === '' || !preg_match('/^[^\/\\\\:*?"<>|]+$/', $name)) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /filemanager/shared/view/' . $parentId);
            exit;
        }

        $canWrite = $this->shared->canUserAccess(
            $parentId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWrite) {
            $_SESSION['error'] = 'No tienes permiso para crear carpetas aquí';
            header('Location: /filemanager/shared/view/' . $parentId);
            exit;
        }

        if ($this->shared->folderExists($parentId, $name)) {
            $_SESSION['error'] = 'Ya existe una carpeta con ese nombre';
            header('Location: /filemanager/shared/view/' . $parentId);
            exit;
        }

        try {
            $parent = $this->shared->findFolderById($parentId);
            $this->shared->createFolder(
                $name,
                $parentId,
                (int) $user['id'],
                $parent['department_id'] ? (int) $parent['department_id'] : null,
                (bool) $parent['is_public']
            );

            $_SESSION['success'] = "Carpeta '$name' creada";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view/' . $parentId);
        exit;
    }

    /**
     * Sube un archivo a una carpeta compartida.
     */
    public function upload(): void
    {
        $user = $this->auth->requireLogin();

        $folderId = (int) ($_POST['folder_id'] ?? 0);

        if ($folderId <= 0) {
            $_SESSION['error'] = 'Carpeta inválida';
            header('Location: /filemanager/shared');
            exit;
        }

        $canWrite = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWrite) {
            $_SESSION['error'] = 'No tienes permiso para subir archivos aquí';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir archivo';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        $file = $_FILES['file'];
        $name = basename($file['name']);
        $size = (int) $file['size'];

        if ($size > 6 * 1024 * 1024) {
            $_SESSION['error'] = 'El archivo supera los 6 MB';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        if ($this->shared->fileExists($folderId, $name)) {
            $_SESSION['error'] = 'Ya existe un archivo con ese nombre';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        $mime = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';

        try {
            $fileId = $this->shared->createFile($folderId, $name, $size, $mime, (int) $user['id']);

            $config = require __DIR__ . '/../../config/config.php';
            $sharedDir = $config['storage']['path'] . '/shared/' . $folderId;
            if (!is_dir($sharedDir)) {
                mkdir($sharedDir, 0755, true);
            }

            $physicalPath = $sharedDir . '/' . $name;
            if (!move_uploaded_file($file['tmp_name'], $physicalPath)) {
                $this->shared->deleteFile($fileId);
                throw new \RuntimeException('No se pudo guardar el archivo en disco');
            }

            $_SESSION['success'] = "Archivo '$name' subido";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view/' . $folderId);
        exit;
    }

    /**
     * Descarga un archivo de una carpeta compartida.
     */
    public function download(int $fileId): void
    {
        $user = $this->auth->requireLogin();

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            http_response_code(404);
            echo 'Archivo no encontrado';
            return;
        }

        $canRead = $this->shared->canUserAccess(
            (int) $file['folder_id'],
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'read'
        );

        if (!$canRead) {
            http_response_code(403);
            echo '403 - Sin acceso';
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $physicalPath = $config['storage']['path'] . '/shared/' . $file['folder_id'] . '/' . $file['name'];

        if (!file_exists($physicalPath)) {
            http_response_code(404);
            echo 'Archivo no existe en disco';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file['name']) . '"');
        header('Content-Length: ' . filesize($physicalPath));
        readfile($physicalPath);
        exit;
    }

    /**
     * Muestra un archivo compartido en el navegador.
     */
    public function viewFile(int $fileId): void
    {
        $user = $this->auth->requireLogin();

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        $canRead = $this->shared->canUserAccess(
            (int) $file['folder_id'],
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'read'
        );

        if (!$canRead) {
            http_response_code(403);
            echo '403 - Sin acceso';
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $physicalPath = $config['storage']['path'] . '/shared/' . $file['folder_id'] . '/' . $file['name'];

        if (!file_exists($physicalPath)) {
            http_response_code(404);
            echo 'Archivo no existe en disco';
            return;
        }

        $content = file_get_contents($physicalPath);
        $size = filesize($physicalPath);
        $mime = mime_content_type($physicalPath) ?: 'application/octet-stream';
        $folder = $this->shared->findFolderById((int) $file['folder_id']);

        require __DIR__ . '/../Views/shared/view_file.php';
    }

    /**
     * Renombra un archivo compartido.
     */
    public function renameFile(): void
    {
        $user = $this->auth->requireLogin();

        $fileId = (int) ($_POST['file_id'] ?? 0);
        $newName = trim($_POST['new_name'] ?? '');

        if ($fileId <= 0 || $newName === '' || !preg_match('/^[^\/\\\\:*?"<>|]+$/', $newName)) {
            $_SESSION['error'] = 'Nombre inválido';
            header('Location: /filemanager/shared');
            exit;
        }

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            $_SESSION['error'] = 'Archivo no encontrado';
            header('Location: /filemanager/shared');
            exit;
        }

        $folderId = (int) $file['folder_id'];

        $canWrite = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWrite) {
            $_SESSION['error'] = 'Sin permiso';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        if ($this->shared->fileExists($folderId, $newName)) {
            $_SESSION['error'] = 'Ya existe un archivo con ese nombre';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $basePath = $config['storage']['path'] . '/shared/' . $folderId;
            $oldPath = $basePath . '/' . $file['name'];
            $newPath = $basePath . '/' . $newName;

            if (file_exists($oldPath)) {
                rename($oldPath, $newPath);
            }

            $this->shared->renameFile($fileId, $newName);
            $_SESSION['success'] = 'Archivo renombrado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view/' . $folderId);
        exit;
    }

    /**
     * Borra un archivo compartido.
     */
    public function deleteFile(): void
    {
        $user = $this->auth->requireLogin();

        $fileId = (int) ($_POST['file_id'] ?? 0);

        if ($fileId <= 0) {
            $_SESSION['error'] = 'Archivo inválido';
            header('Location: /filemanager/shared');
            exit;
        }

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            $_SESSION['error'] = 'Archivo no encontrado';
            header('Location: /filemanager/shared');
            exit;
        }

        $folderId = (int) $file['folder_id'];

        $canDelete = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'delete'
        );

        if (!$canDelete) {
            $_SESSION['error'] = 'Sin permiso para borrar';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $physicalPath = $config['storage']['path'] . '/shared/' . $folderId . '/' . $file['name'];
            if (file_exists($physicalPath)) {
                unlink($physicalPath);
            }

            $this->shared->deleteFile($fileId);
            $_SESSION['success'] = 'Archivo eliminado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view/' . $folderId);
        exit;
    }

    /**
     * Borra una subcarpeta compartida (recursivo).
     */
    public function deleteFolder(): void
    {
        $user = $this->auth->requireLogin();

        $folderId = (int) ($_POST['folder_id'] ?? 0);

        if ($folderId <= 0) {
            $_SESSION['error'] = 'Carpeta inválida';
            header('Location: /filemanager/shared');
            exit;
        }

        $folder = $this->shared->findFolderById($folderId);
        if ($folder === null) {
            $_SESSION['error'] = 'Carpeta no encontrada';
            header('Location: /filemanager/shared');
            exit;
        }

        $parentId = $folder['parent_id'] ? (int) $folder['parent_id'] : null;
        $redirect = $parentId ? '/filemanager/shared/view/' . $parentId : '/filemanager/shared';

        $canDelete = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'delete'
        );

        if (!$canDelete) {
            $_SESSION['error'] = 'Sin permiso para borrar';
            header('Location: ' . $redirect);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $physicalPath = $config['storage']['path'] . '/shared/' . $folderId;
            if (is_dir($physicalPath)) {
                $this->deleteDirectoryRecursive($physicalPath);
            }

            $this->shared->deleteFolder($folderId);
            $_SESSION['success'] = 'Carpeta eliminada';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . $redirect);
        exit;
    }
        /**
     * Guarda el contenido editado de un archivo de texto.
     */
    public function saveFile(): void
    {
        $user = $this->auth->requireLogin();

        $fileId = (int) ($_POST['file_id'] ?? 0);
        $content = $_POST['content'] ?? '';

        if ($fileId <= 0) {
            $_SESSION['error'] = 'Archivo inválido';
            header('Location: /filemanager/shared');
            exit;
        }

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            $_SESSION['error'] = 'Archivo no encontrado';
            header('Location: /filemanager/shared');
            exit;
        }

        $folderId = (int) $file['folder_id'];

        $canWrite = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWrite) {
            $_SESSION['error'] = 'Sin permiso para editar';
            header('Location: /filemanager/shared/view-file/' . $fileId);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $physicalPath = $config['storage']['path'] . '/shared/' . $folderId . '/' . $file['name'];

            if (file_put_contents($physicalPath, $content) === false) {
                throw new \RuntimeException('No se pudo guardar el archivo');
            }

            $newSize = strlen($content);
            $this->shared->updateFile(
                $fileId,
                $newSize,
                $file['mime_type'],
                (int) $user['id']
            );

            $_SESSION['success'] = 'Archivo guardado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view-file/' . $fileId);
        exit;
    }

    /**
     * Reemplaza un archivo subiendo uno nuevo.
     */
    public function replaceFile(): void
    {
        $user = $this->auth->requireLogin();

        $fileId = (int) ($_POST['file_id'] ?? 0);

        if ($fileId <= 0) {
            $_SESSION['error'] = 'Archivo inválido';
            header('Location: /filemanager/shared');
            exit;
        }

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            $_SESSION['error'] = 'Archivo no encontrado';
            header('Location: /filemanager/shared');
            exit;
        }

        $folderId = (int) $file['folder_id'];

        $canWrite = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWrite) {
            $_SESSION['error'] = 'Sin permiso para reemplazar';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir archivo';
            header('Location: /filemanager/shared/view-file/' . $fileId);
            exit;
        }

        $newFile = $_FILES['file'];
        $size = (int) $newFile['size'];

        if ($size > 6 * 1024 * 1024) {
            $_SESSION['error'] = 'El archivo supera los 6 MB';
            header('Location: /filemanager/shared/view-file/' . $fileId);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $folderPath = $config['storage']['path'] . '/shared/' . $folderId;

            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0755, true);
            }

            $physicalPath = $folderPath . '/' . $file['name'];

            // Borrar el archivo antiguo si existe
            if (file_exists($physicalPath)) {
                unlink($physicalPath);
            }

            if (!move_uploaded_file($newFile['tmp_name'], $physicalPath)) {
                throw new \RuntimeException('No se pudo guardar el archivo');
            }

            $newMime = mime_content_type($physicalPath) ?: 'application/octet-stream';

            $this->shared->updateFile(
                $fileId,
                $size,
                $newMime,
                (int) $user['id']
            );

            $_SESSION['success'] = 'Archivo reemplazado';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view-file/' . $fileId);
        exit;
    }

    /**
     * Verifica si un archivo es texto editable.
     * Detección por MIME + extensión (Opción C).
     */
    private function isEditableText(array $file): bool
    {
        $mime = $file['mime_type'] ?? '';
        $name = $file['name'] ?? '';

        // Por MIME
        if (str_starts_with($mime, 'text/')) return true;
        if (in_array($mime, ['application/json', 'application/xml', 'application/javascript', 'application/x-httpd-php'], true)) return true;

        // Por extensión
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $editableExtensions = [
            'txt', 'md', 'json', 'csv', 'xml', 'html', 'htm',
            'css', 'js', 'ts', 'sql', 'log', 'yml', 'yaml',
            'ini', 'conf', 'env', 'php', 'py', 'sh', 'bat', 'gitignore',
        ];
        if (in_array($ext, $editableExtensions, true)) return true;

        return false;
    }
        /**
     * Muestra el modal de mover archivo (devuelve JSON con carpetas).
     */
    public function listTargetFolders(): void
    {
        $user = $this->auth->requireLogin();

        $folders = $this->shared->findWritableFolders(
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin'
        );

        header('Content-Type: application/json');
        echo json_encode(['folders' => $folders]);
        exit;
    }

    /**
     * Mueve un archivo a otra carpeta.
     */
    public function moveFile(): void
    {
        $user = $this->auth->requireLogin();

        $fileId = (int) ($_POST['file_id'] ?? 0);
        $targetFolderId = (int) ($_POST['target_folder_id'] ?? 0);

        if ($fileId <= 0 || $targetFolderId <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /filemanager/shared');
            exit;
        }

        $file = $this->shared->findFileById($fileId);
        if ($file === null) {
            $_SESSION['error'] = 'Archivo no encontrado';
            header('Location: /filemanager/shared');
            exit;
        }

        $sourceFolderId = (int) $file['folder_id'];

        // Permiso en origen
        $canWriteSource = $this->shared->canUserAccess(
            $sourceFolderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        // Permiso en destino
        $canWriteTarget = $this->shared->canUserAccess(
            $targetFolderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        if (!$canWriteSource || !$canWriteTarget) {
            $_SESSION['error'] = 'Sin permisos para mover';
            header('Location: /filemanager/shared/view/' . $sourceFolderId);
            exit;
        }

        if ($sourceFolderId === $targetFolderId) {
            $_SESSION['error'] = 'El archivo ya está en esa carpeta';
            header('Location: /filemanager/shared/view/' . $sourceFolderId);
            exit;
        }

        if ($this->shared->fileExists($targetFolderId, $file['name'])) {
            $_SESSION['error'] = 'Ya existe un archivo con ese nombre en la carpeta destino';
            header('Location: /filemanager/shared/view/' . $sourceFolderId);
            exit;
        }

        try {
            $config = require __DIR__ . '/../../config/config.php';
            $storageBase = $config['storage']['path'] . '/shared';

            $oldPath = $storageBase . '/' . $sourceFolderId . '/' . $file['name'];
            $targetDir = $storageBase . '/' . $targetFolderId;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $newPath = $targetDir . '/' . $file['name'];

            if (file_exists($oldPath)) {
                if (!rename($oldPath, $newPath)) {
                    throw new \RuntimeException('No se pudo mover el archivo en disco');
                }
            }

            $this->shared->moveFile($fileId, $targetFolderId);
            $_SESSION['success'] = "Archivo '{$file['name']}' movido";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: /filemanager/shared/view/' . $sourceFolderId);
        exit;
    }

    /**
     * Mueve una carpeta a otro padre.
     */
    public function moveFolder(): void
    {
        $user = $this->auth->requireLogin();

        $folderId = (int) ($_POST['folder_id'] ?? 0);
        $targetParentId = (int) ($_POST['target_parent_id'] ?? 0);

        if ($folderId <= 0) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: /filemanager/shared');
            exit;
        }

        $folder = $this->shared->findFolderById($folderId);
        if ($folder === null) {
            $_SESSION['error'] = 'Carpeta no encontrada';
            header('Location: /filemanager/shared');
            exit;
        }

        $currentParentId = $folder['parent_id'] ? (int) $folder['parent_id'] : 0;
        $targetParentIdOrNull = $targetParentId === 0 ? null : $targetParentId;

        if ($currentParentId === $targetParentId) {
            $_SESSION['error'] = 'La carpeta ya está ahí';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        // Evitar ciclos: no puedes mover una carpeta dentro de sí misma o de sus descendientes
        if ($targetParentId !== 0 && $this->shared->isDescendant($targetParentId, $folderId)) {
            $_SESSION['error'] = 'No puedes mover una carpeta dentro de sí misma o de sus subcarpetas';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        // Verificar permiso de escritura en el padre actual y en el destino
        $canWriteCurrent = $this->shared->canUserAccess(
            $folderId,
            (int) $user['id'],
            $user['department_id'] ? (int) $user['department_id'] : null,
            $user['role'] === 'admin',
            'write'
        );

        $canWriteTarget = $targetParentId === 0
            ? true
            : $this->shared->canUserAccess(
                $targetParentId,
                (int) $user['id'],
                $user['department_id'] ? (int) $user['department_id'] : null,
                $user['role'] === 'admin',
                'write'
            );

        if (!$canWriteCurrent || !$canWriteTarget) {
            $_SESSION['error'] = 'Sin permisos para mover';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        // Verificar que no exista otra carpeta con el mismo nombre en el destino
        if ($this->shared->folderExists($targetParentIdOrNull, $folder['name'])) {
            $_SESSION['error'] = 'Ya existe una carpeta con ese nombre en el destino';
            header('Location: /filemanager/shared/view/' . $folderId);
            exit;
        }

        try {
            $this->shared->moveFolder($folderId, $targetParentIdOrNull);
            $_SESSION['success'] = "Carpeta '{$folder['name']}' movida";
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        $redirect = $currentParentId > 0
            ? '/filemanager/shared/view/' . $currentParentId
            : '/filemanager/shared';
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Borra una carpeta recursivamente.
     */
    private function deleteDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectoryRecursive($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
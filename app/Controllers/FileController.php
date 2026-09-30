<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Filesystem\NodeRepository;
use App\Filesystem\PathResolver;
use App\Filesystem\PathNotFoundException;
use App\Filesystem\Storage;

final class FileController
{
    private Auth $auth;
    private NodeRepository $nodes;
    private PathResolver $resolver;
    private Storage $storage;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->nodes = new NodeRepository();
        $this->resolver = new PathResolver();
        $this->storage = new Storage();
    }

    /**
     * Explorador en la raíz o en una ruta específica.
     */
    public function index(string $path = '/'): void
    {
        $user = $this->requireLogin();

        try {
            $resolved = $this->resolver->resolve($user['id'], $path);
        } catch (PathNotFoundException $e) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        if ($resolved->type === 'file') {
            $this->download($path);
            return;
        }

        $children = $this->nodes->findChildren($user['id'], $resolved->nodeId);
        $usedBytes = $this->nodes->getUsedBytes($user['id']);

        require __DIR__ . '/../Views/files/explorer.php';
    }

    /**
     * Muestra el contenido de un archivo en el navegador.
     */
    public function view(string $path): void
    {
        $user = $this->requireLogin();

        try {
            $resolved = $this->resolver->resolve($user['id'], $path);
        } catch (PathNotFoundException $e) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        if ($resolved->type !== 'file') {
            http_response_code(400);
            echo 'No es un archivo';
            return;
        }

        if (!file_exists($resolved->physicalPath)) {
            http_response_code(404);
            echo 'Archivo no existe en disco';
            return;
        }

        $content = file_get_contents($resolved->physicalPath);
        $size = filesize($resolved->physicalPath);
        $mime = mime_content_type($resolved->physicalPath) ?: 'application/octet-stream';

        require __DIR__ . '/../Views/files/view.php';
    }

    /**
     * Busca archivos y carpetas por nombre.
     */
    public function search(): void
    {
        $user = $this->requireLogin();

        $query = trim($_GET['q'] ?? '');

        if ($query === '') {
            header('Location: /filemanager/');
            exit;
        }

        if (strlen($query) > 100) {
            $query = substr($query, 0, 100);
        }

        $results = $this->nodes->search($user['id'], $query);

        foreach ($results as &$item) {
            $item['full_path'] = $this->buildNodePath($item);
        }
        unset($item);

        $usedBytes = $this->nodes->getUsedBytes($user['id']);

        require __DIR__ . '/../Views/files/search.php';
    }

    /**
     * Construye la ruta lógica completa de un nodo subiendo por parent_id.
     */
    private function buildNodePath(array $node): string
    {
        $parts = [$node['name']];
        $parentId = $node['parent_id'];

        while ($parentId !== null) {
            $parent = $this->nodes->findById((int) $parentId, (int) $node['user_id']);
            if ($parent === null) break;
            array_unshift($parts, $parent['name']);
            $parentId = $parent['parent_id'];
        }

        return '/' . implode('/', $parts);
    }

    /**
     * Crea una carpeta.
     */
    public function createFolder(): void
    {
        $user = $this->requireLogin();

        $path = $_POST['path'] ?? '/';
        $name = trim($_POST['name'] ?? '');

        if ($name === '' || !preg_match('/^[^\/\\\\:*?"<>|]+$/', $name)) {
            $_SESSION['error'] = 'Nombre de carpeta inválido';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $resolved = $this->resolver->resolve($user['id'], $path);
        } catch (PathNotFoundException $e) {
            $_SESSION['error'] = 'Ruta no encontrada';
            header('Location: /filemanager/');
            exit;
        }

        if ($this->nodes->exists($user['id'], $resolved->nodeId, $name)) {
            $_SESSION['error'] = 'Ya existe un elemento con ese nombre';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $nodeId = $this->nodes->create($user['id'], $resolved->nodeId, $name, 'folder');
            $physicalPath = $resolved->physicalPath . '/' . $name;
            $this->storage->createFolder($physicalPath);
        } catch (\Throwable $e) {
            if (isset($nodeId)) {
                $this->nodes->delete($nodeId, $user['id']);
            }
            $_SESSION['error'] = 'Error al crear carpeta: ' . $e->getMessage();
        }

        header('Location: /filemanager/files' . $path);
        exit;
    }

    /**
     * Sube un archivo.
     */
    public function upload(): void
    {
        $user = $this->requireLogin();

        $path = $_POST['path'] ?? '/';

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir archivo';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        $file = $_FILES['file'];
        $name = basename($file['name']);
        $size = $file['size'];

        if ($size > 6 * 1024 * 1024) {
            $_SESSION['error'] = 'El archivo supera los 6 MB';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $resolved = $this->resolver->resolve($user['id'], $path);
        } catch (PathNotFoundException $e) {
            $_SESSION['error'] = 'Ruta no encontrada';
            header('Location: /filemanager/');
            exit;
        }

        if ($this->nodes->exists($user['id'], $resolved->nodeId, $name)) {
            $_SESSION['error'] = 'Ya existe un archivo con ese nombre';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        $usedBytes = $this->nodes->getUsedBytes($user['id']);
        if ($usedBytes + $size > $user['quota_bytes']) {
            $_SESSION['error'] = 'Cuota excedida';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        $mime = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';

        try {
            $nodeId = $this->nodes->create($user['id'], $resolved->nodeId, $name, 'file', $size, $mime);

            $physicalPath = $resolved->physicalPath . '/' . $name;
            $content = file_get_contents($file['tmp_name']);
            $this->storage->writeFile($physicalPath, $content);
        } catch (\Throwable $e) {
            if (isset($nodeId)) {
                $this->nodes->delete($nodeId, $user['id']);
            }
            $_SESSION['error'] = 'Error al subir archivo: ' . $e->getMessage();
        }

        header('Location: /filemanager/files' . $path);
        exit;
    }

    /**
     * Renombra un nodo.
     */
    public function rename(): void
    {
        $user = $this->requireLogin();

        $path = $_POST['path'] ?? '/';
        $oldName = $_POST['old_name'] ?? '';
        $newName = trim($_POST['new_name'] ?? '');

        if ($oldName === '' || $newName === '') {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $parent = $this->resolver->resolve($user['id'], $path);
            $node = $this->resolver->resolve($user['id'], rtrim($path, '/') . '/' . $oldName);
        } catch (PathNotFoundException $e) {
            $_SESSION['error'] = 'No encontrado';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        if ($this->nodes->exists($user['id'], $parent->nodeId, $newName)) {
            $_SESSION['error'] = 'Ya existe un elemento con ese nombre';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $this->nodes->rename($node->nodeId, $user['id'], $newName);
            $this->storage->move(
                $node->physicalPath,
                $parent->physicalPath . '/' . $newName
            );
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error al renombrar: ' . $e->getMessage();
        }

        header('Location: /filemanager/files' . $path);
        exit;
    }

    /**
     * Borra un nodo (recursivo).
     */
    public function delete(): void
    {
        $user = $this->requireLogin();

        $path = $_POST['path'] ?? '/';
        $name = $_POST['name'] ?? '';

        if ($name === '') {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $node = $this->resolver->resolve($user['id'], rtrim($path, '/') . '/' . $name);
        } catch (PathNotFoundException $e) {
            $_SESSION['error'] = 'No encontrado';
            header('Location: /filemanager/files' . $path);
            exit;
        }

        try {
            $this->storage->delete($node->physicalPath);
            $this->nodes->delete($node->nodeId, $user['id']);
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error al borrar: ' . $e->getMessage();
        }

        header('Location: /filemanager/files' . $path);
        exit;
    }

    /**
     * Descarga un archivo.
     */
    public function download(string $path): void
    {
        $user = $this->requireLogin();

        try {
            $resolved = $this->resolver->resolve($user['id'], $path);
        } catch (PathNotFoundException $e) {
            http_response_code(404);
            echo 'No encontrado';
            return;
        }

        if ($resolved->type !== 'file') {
            http_response_code(400);
            echo 'No es un archivo';
            return;
        }

        if (!file_exists($resolved->physicalPath)) {
            http_response_code(404);
            echo 'Archivo no existe en disco';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($resolved->name) . '"');
        header('Content-Length: ' . filesize($resolved->physicalPath));
        readfile($resolved->physicalPath);
        exit;
    }

    /**
     * Verifica que haya sesión. Si no, redirige a /login.
     */
    private function requireLogin(): array
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            header('Location: /filemanager/login');
            exit;
        }
        return $user;
    }
}
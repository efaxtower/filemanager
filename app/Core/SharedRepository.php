<?php

namespace App\Core;

use App\Core\Database;
use PDO;

final class SharedRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // ============================================================
    // CARPETAS
    // ============================================================

    /**
     * Busca una carpeta por id.
     */
    public function findFolderById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, d.name AS department_name, u.username AS owner_name
             FROM shared_folders f
             LEFT JOIN departments d ON f.department_id = d.id
             LEFT JOIN users u ON f.owner_id = u.id
             WHERE f.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Hijos directos de una carpeta (o raíz si parentId es null).
     */
    public function findChildren(?int $parentId): array
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT f.*, d.name AS department_name, u.username AS owner_name
                 FROM shared_folders f
                 LEFT JOIN departments d ON f.department_id = d.id
                 LEFT JOIN users u ON f.owner_id = u.id
                 WHERE f.parent_id IS NULL
                 ORDER BY f.name ASC'
            );
            $stmt->execute();
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT f.*, d.name AS department_name, u.username AS owner_name
                 FROM shared_folders f
                 LEFT JOIN departments d ON f.department_id = d.id
                 LEFT JOIN users u ON f.owner_id = u.id
                 WHERE f.parent_id = ?
                 ORDER BY f.name ASC'
            );
            $stmt->execute([$parentId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Hijos de una carpeta filtrados por lo que un usuario puede ver.
     */
    public function findChildrenForUser(?int $parentId, int $userId, ?int $departmentId, bool $isAdmin): array
    {
        if ($isAdmin) {
            return $this->findChildren($parentId);
        }

        $sql = 'SELECT f.*, d.name AS department_name, u.username AS owner_name
                FROM shared_folders f
                LEFT JOIN departments d ON f.department_id = d.id
                LEFT JOIN users u ON f.owner_id = u.id
                WHERE ' . ($parentId === null ? 'f.parent_id IS NULL' : 'f.parent_id = ?') . '
                AND (
                    f.is_public = 1
                    OR f.department_id = ?
                    OR EXISTS (
                        SELECT 1 FROM shared_permissions p
                        WHERE p.folder_id = f.id AND p.user_id = ? AND p.can_read = 1
                    )
                )
                ORDER BY f.name ASC';

        $params = [];
        if ($parentId !== null) $params[] = $parentId;
        $params[] = $departmentId ?? 0;
        $params[] = $userId;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Crea una carpeta compartida.
     */
    public function createFolder(
        string $name,
        ?int $parentId,
        int $ownerId,
        ?int $departmentId,
        bool $isPublic = false
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shared_folders (name, parent_id, owner_id, department_id, is_public)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $parentId, $ownerId, $departmentId, $isPublic ? 1 : 0]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Verifica si ya existe una carpeta con ese nombre en ese padre.
     */
    public function folderExists(?int $parentId, string $name): bool
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM shared_folders WHERE parent_id IS NULL AND name = ? LIMIT 1'
            );
            $stmt->execute([$name]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM shared_folders WHERE parent_id = ? AND name = ? LIMIT 1'
            );
            $stmt->execute([$parentId, $name]);
        }
        return $stmt->fetch() !== false;
    }

    /**
     * Renombra una carpeta.
     */
    public function renameFolder(int $id, string $newName): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shared_folders SET name = ? WHERE id = ?'
        );
        $stmt->execute([$newName, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Cambia departamento y visibilidad de una carpeta.
     */
    public function updateFolderMeta(int $id, ?int $departmentId, bool $isPublic): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shared_folders SET department_id = ?, is_public = ? WHERE id = ?'
        );
        $stmt->execute([$departmentId, $isPublic ? 1 : 0, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Borra una carpeta (cascada borra hijos y permisos).
     */
    public function deleteFolder(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM shared_folders WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    // ============================================================
    // PERMISOS
    // ============================================================

    /**
     * Lista todos los permisos de una carpeta con datos del usuario.
     */
    public function findPermissions(int $folderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.username, u.role, d.name AS department_name
             FROM shared_permissions p
             INNER JOIN users u ON p.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE p.folder_id = ?
             ORDER BY u.username ASC'
        );
        $stmt->execute([$folderId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca un permiso específico.
     */
    public function findPermission(int $folderId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM shared_permissions WHERE folder_id = ? AND user_id = ?'
        );
        $stmt->execute([$folderId, $userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Crea o actualiza un permiso.
     */
    public function setPermission(
        int $folderId,
        int $userId,
        bool $canRead,
        bool $canWrite,
        bool $canDelete
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shared_permissions (folder_id, user_id, can_read, can_write, can_delete)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                can_read = VALUES(can_read),
                can_write = VALUES(can_write),
                can_delete = VALUES(can_delete)'
        );
        $stmt->execute([
            $folderId,
            $userId,
            $canRead ? 1 : 0,
            $canWrite ? 1 : 0,
            $canDelete ? 1 : 0
        ]);
    }

    /**
     * Borra un permiso.
     */
    public function deletePermission(int $folderId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM shared_permissions WHERE folder_id = ? AND user_id = ?'
        );
        $stmt->execute([$folderId, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * ¿Un usuario puede hacer X en una carpeta?
     */
    public function canUserAccess(int $folderId, int $userId, ?int $departmentId, bool $isAdmin, string $action = 'read'): bool
    {
        if ($isAdmin) return true;

        $folder = $this->findFolderById($folderId);
        if ($folder === null) return false;

        // Pública
        if ((int) $folder['is_public'] === 1 && $action === 'read') return true;

        // Es de su departamento
        if ($departmentId !== null && (int) $folder['department_id'] === $departmentId) {
            return true;
        }

        // Permiso individual
        $perm = $this->findPermission($folderId, $userId);
        if ($perm !== null) {
            return match($action) {
                'read' => (bool) $perm['can_read'],
                'write' => (bool) $perm['can_write'],
                'delete' => (bool) $perm['can_delete'],
                default => false,
            };
        }

        return false;
    }

    /**
     * Cuenta cuántas carpetas compartidas existen.
     */
    public function countFolders(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM shared_folders');
        $row = $stmt->fetch();
        return (int) $row['total'];
    }
        // ============================================================
    // ARCHIVOS
    // ============================================================

    /**
     * Lista los archivos de una carpeta compartida.
     */
    public function findFiles(int $folderId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, u.username AS uploader_name
             FROM shared_files f
             LEFT JOIN users u ON f.uploaded_by = u.id
             WHERE f.folder_id = ?
             ORDER BY f.name ASC'
        );
        $stmt->execute([$folderId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca un archivo por id.
     */
    public function findFileById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT f.*, u.username AS uploader_name
             FROM shared_files f
             LEFT JOIN users u ON f.uploaded_by = u.id
             WHERE f.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Verifica si ya existe un archivo con ese nombre en esa carpeta.
     */
    public function fileExists(int $folderId, string $name): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM shared_files WHERE folder_id = ? AND name = ? LIMIT 1'
        );
        $stmt->execute([$folderId, $name]);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea un archivo.
     */
    public function createFile(
        int $folderId,
        string $name,
        int $sizeBytes,
        ?string $mimeType,
        int $uploadedBy
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shared_files (folder_id, name, size_bytes, mime_type, uploaded_by)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$folderId, $name, $sizeBytes, $mimeType, $uploadedBy]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Renombra un archivo.
     */
    public function renameFile(int $id, string $newName): bool
    {
        $stmt = $this->pdo->prepare('UPDATE shared_files SET name = ? WHERE id = ?');
        $stmt->execute([$newName, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Borra un archivo.
     */
    public function deleteFile(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM shared_files WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Calcula el tamaño total de una carpeta compartida (recursivo).
     */
    public function getFolderSize(int $folderId): int
    {
        // Archivos directos
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(size_bytes), 0) AS total FROM shared_files WHERE folder_id = ?'
        );
        $stmt->execute([$folderId]);
        $row = $stmt->fetch();
        $total = (int) $row['total'];

        // Subcarpetas
        $children = $this->findChildren($folderId);
        foreach ($children as $child) {
            $total += $this->getFolderSize((int) $child['id']);
        }
        return $total;
    }
        /**
     * Actualiza los metadatos de un archivo (al reemplazar).
     */
    public function updateFile(
        int $id,
        int $sizeBytes,
        ?string $mimeType,
        int $uploadedBy
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE shared_files 
             SET size_bytes = ?, mime_type = ?, uploaded_by = ?, created_at = CURRENT_TIMESTAMP 
             WHERE id = ?'
        );
        $stmt->execute([$sizeBytes, $mimeType, $uploadedBy, $id]);
        return $stmt->rowCount() > 0;
    }
        /**
     * Devuelve todas las carpetas donde un usuario puede escribir,
     * ordenadas jerárquicamente para el dropdown de "mover".
     */
    public function findWritableFolders(int $userId, ?int $departmentId, bool $isAdmin): array
    {
        if ($isAdmin) {
            $stmt = $this->pdo->query(
                'SELECT id, name, parent_id FROM shared_folders ORDER BY name ASC'
            );
            $folders = $stmt->fetchAll();
        } else {
            $sql = 'SELECT f.id, f.name, f.parent_id
                    FROM shared_folders f
                    WHERE (
                        f.is_public = 1
                        OR f.department_id = ?
                        OR EXISTS (
                            SELECT 1 FROM shared_permissions p
                            WHERE p.folder_id = f.id AND p.user_id = ? AND p.can_write = 1
                        )
                    )
                    ORDER BY f.name ASC';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$departmentId ?? 0, $userId]);
            $folders = $stmt->fetchAll();
        }

        // Construir árbol jerárquico con nivel
        return $this->buildFolderTree($folders);
    }

    /**
     * Convierte una lista plana de carpetas en una lista con nivel (profundidad)
     * y ruta completa.
     */
    private function buildFolderTree(array $folders): array
    {
        // Indexar por id
        $byId = [];
        foreach ($folders as $f) {
            $byId[(int) $f['id']] = $f;
        }

        // Calcular ruta completa subiendo por parent_id
        $getPath = function(int $id) use (&$byId): string {
            $parts = [];
            $current = $id;
            $safety = 0;
            while ($current !== 0 && isset($byId[$current]) && $safety < 50) {
                array_unshift($parts, $byId[$current]['name']);
                $parent = $byId[$current]['parent_id'];
                $current = $parent ? (int) $parent : 0;
                $safety++;
            }
            return '/ ' . implode(' / ', $parts);
        };

        // Calcular nivel (profundidad)
        $getLevel = function(int $id) use (&$byId): int {
            $level = 0;
            $current = $id;
            $safety = 0;
            while (isset($byId[$current]) && $byId[$current]['parent_id'] && $safety < 50) {
                $current = (int) $byId[$current]['parent_id'];
                $level++;
                $safety++;
            }
            return $level;
        };

        // Añadir path y level a cada carpeta
        foreach ($folders as &$f) {
            $f['path'] = $getPath((int) $f['id']);
            $f['level'] = $getLevel((int) $f['id']);
        }
        unset($f);

        // Ordenar por path
        usort($folders, fn($a, $b) => strcmp($a['path'], $b['path']));

        return $folders;
    }

    /**
     * Mueve un archivo a otra carpeta.
     */
    public function moveFile(int $fileId, int $targetFolderId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shared_files SET folder_id = ? WHERE id = ?'
        );
        $stmt->execute([$targetFolderId, $fileId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Mueve una carpeta a otro padre.
     */
    public function moveFolder(int $folderId, ?int $targetParentId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shared_folders SET parent_id = ? WHERE id = ?'
        );
        $stmt->execute([$targetParentId, $folderId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica si un nodo es descendiente de otro (para evitar ciclos al mover carpetas).
     */
    public function isDescendant(int $folderId, int $possibleAncestorId): bool
    {
        $current = $folderId;
        $safety = 0;
        while ($current !== 0 && $safety < 100) {
            if ($current === $possibleAncestorId) return true;
            $folder = $this->findFolderById($current);
            if ($folder === null) break;
            $current = $folder['parent_id'] ? (int) $folder['parent_id'] : 0;
            $safety++;
        }
        return false;
    }
}
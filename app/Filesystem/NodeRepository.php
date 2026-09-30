<?php

namespace App\Filesystem;

use App\Core\Database;
use PDO;

final class NodeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM nodes WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findChildren(int $userId, ?int $parentId): array
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM nodes 
                 WHERE user_id = ? AND parent_id IS NULL 
                 ORDER BY type DESC, name ASC'
            );
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM nodes 
                 WHERE user_id = ? AND parent_id = ? 
                 ORDER BY type DESC, name ASC'
            );
            $stmt->execute([$userId, $parentId]);
        }

        return $stmt->fetchAll();
    }

    public function exists(int $userId, ?int $parentId, string $name): bool
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM nodes WHERE user_id = ? AND parent_id IS NULL AND name = ? LIMIT 1'
            );
            $stmt->execute([$userId, $name]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM nodes WHERE user_id = ? AND parent_id = ? AND name = ? LIMIT 1'
            );
            $stmt->execute([$userId, $parentId, $name]);
        }

        return $stmt->fetch() !== false;
    }

    public function getUsedBytes(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(size_bytes), 0) AS total 
             FROM nodes 
             WHERE user_id = ? AND type = ?'
        );
        $stmt->execute([$userId, 'file']);

        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    public function create(
        int $userId,
        ?int $parentId,
        string $name,
        string $type,
        int $sizeBytes = 0,
        ?string $mimeType = null
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO nodes (user_id, parent_id, name, type, size_bytes, mime_type) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $parentId, $name, $type, $sizeBytes, $mimeType]);

        return (int) $this->pdo->lastInsertId();
    }

    public function rename(int $id, int $userId, string $newName): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE nodes SET name = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$newName, $id, $userId]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM nodes WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $userId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Busca nodos por nombre (LIKE) en todo el árbol del usuario.
     * Devuelve máximo 100 resultados.
     */
    public function search(int $userId, string $query): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM nodes 
             WHERE user_id = ? AND name LIKE ? 
             ORDER BY type DESC, name ASC 
             LIMIT 100'
        );
        $stmt->execute([$userId, '%' . $query . '%']);

        return $stmt->fetchAll();
    }
}
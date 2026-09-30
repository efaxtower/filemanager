<?php

namespace App\Filesystem;

use App\Core\Database;
use PDO;

final class PathResolver
{
    private PDO $pdo;
    private string $storageBase;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->pdo = Database::getConnection();
        $this->storageBase = $config['storage']['path'];
    }

    /**
     * Normaliza una ruta lógica y la convierte en array de segmentos.
     * 
     * @return string[] Array de segmentos limpios
     * @throws PathNotFoundException si la ruta es inválida
     */
    public function normalize(string $logicalPath): array
    {
        // 1. Asegurar que empieza con /
        if (!str_starts_with($logicalPath, '/')) {
            $logicalPath = '/' . $logicalPath;
        }

        // 2. Quitar / final si existe (excepto si es solo "/")
        if ($logicalPath !== '/' && str_ends_with($logicalPath, '/')) {
            $logicalPath = rtrim($logicalPath, '/');
        }

        // 3. Partir por /
        $segments = explode('/', trim($logicalPath, '/'));

        // 4. Filtrar segmentos vacíos (por // dobles)
        $segments = array_filter($segments, fn($s) => $s !== '');
        $segments = array_values($segments); // reindexar

        // 5. Rechazar segmentos peligrosos
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new PathNotFoundException("Ruta inválida: contiene '{$segment}'");
            }
            if (str_contains($segment, "\0")) {
                throw new PathNotFoundException("Ruta inválida: contiene byte nulo");
            }
        }

        return $segments;
    }

    /**
     * Busca un nodo hijo por nombre dentro de un padre.
     * 
     * @return array|null El nodo como array asociativo, o null si no existe
     */
    private function findChild(int $userId, ?int $parentId, string $name): ?array
    {
        if ($parentId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM nodes WHERE user_id = ? AND parent_id IS NULL AND name = ?'
            );
            $stmt->execute([$userId, $name]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM nodes WHERE user_id = ? AND parent_id = ? AND name = ?'
            );
            $stmt->execute([$userId, $parentId, $name]);
        }

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

        /**
     * Resuelve una ruta lógica a un objeto ResolvedPath.
     * 
     * @throws PathNotFoundException si algún segmento no existe
     */
    public function resolve(int $userId, string $logicalPath): ResolvedPath
    {
        $segments = $this->normalize($logicalPath);

        // Caso especial: raíz del usuario
        if (empty($segments)) {
            return new ResolvedPath(
                nodeId: null,
                parentId: null,
                segments: [],
                logicalPath: '/',
                physicalPath: $this->storageBase . '/users/' . $userId,
                name: null,
                type: null,
            );
        }

        // Caminar el árbol desde la raíz
        $parentId = null;
        $currentNode = null;

        foreach ($segments as $segment) {
            $currentNode = $this->findChild($userId, $parentId, $segment);
            if ($currentNode === null) {
                throw new PathNotFoundException(
                    "No existe: /" . implode('/', $segments)
                );
            }
            $parentId = (int) $currentNode['id'];
        }

        // Construir ruta física
        $physicalPath = $this->storageBase . '/users/' . $userId . '/' . implode('/', $segments);

        return new ResolvedPath(
            nodeId: (int) $currentNode['id'],
            parentId: $currentNode['parent_id'] !== null ? (int) $currentNode['parent_id'] : null,
            segments: $segments,
            logicalPath: '/' . implode('/', $segments),
            physicalPath: $physicalPath,
            name: $currentNode['name'],
            type: $currentNode['type'],
        );
    }
}
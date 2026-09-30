<?php

namespace App\Auth;

use App\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE username = ?'
        );
        $stmt->execute([$username]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE id = ?'
        );
        $stmt->execute([$id]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function exists(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM users WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);

        return $stmt->fetch() !== false;
    }

    public function create(string $username, string $passwordHash, string $role = 'user'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash, role) 
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $passwordHash, $role]);

        return (int) $this->pdo->lastInsertId();
    }
        /**
     * Lista todos los usuarios con el nombre del departamento.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.id, u.username, u.role, u.department_id, u.quota_bytes, u.created_at,
                    d.name AS department_name
             FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             ORDER BY u.username ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Actualiza el rol de un usuario.
     */
    public function updateRole(int $id, string $role): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET role = ? WHERE id = ?'
        );
        $stmt->execute([$role, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualiza el departamento de un usuario.
     */
    public function updateDepartment(int $id, ?int $departmentId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET department_id = ? WHERE id = ?'
        );
        $stmt->execute([$departmentId, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualiza la cuota de un usuario.
     */
    public function updateQuota(int $id, int $bytes): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET quota_bytes = ? WHERE id = ?'
        );
        $stmt->execute([$bytes, $id]);
        return $stmt->rowCount() > 0;
    }
        /**
     * Borra un usuario. Los nodos se borran en cascada.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualiza la contraseña de un usuario.
     */
    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$passwordHash, $id]);
        return $stmt->rowCount() > 0;
    }
}
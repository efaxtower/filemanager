<?php

namespace App\Core;

use App\Core\Database;
use PDO;

final class DepartmentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM departments ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM departments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function exists(string $name): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM departments WHERE name = ? LIMIT 1'
        );
        $stmt->execute([$name]);
        return $stmt->fetch() !== false;
    }

    public function create(string $name, ?string $description = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO departments (name, description) VALUES (?, ?)'
        );
        $stmt->execute([$name, $description]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, ?string $description): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE departments SET name = ?, description = ? WHERE id = ?'
        );
        $stmt->execute([$name, $description, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM departments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function countUsers(int $departmentId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total FROM users WHERE department_id = ?'
        );
        $stmt->execute([$departmentId]);
        $row = $stmt->fetch();
        return (int) $row['total'];
    }
}
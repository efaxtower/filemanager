<?php

namespace App\Core;

use App\Core\Database;
use PDO;

final class AccountRequestRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, d.name AS department_name, u.username AS reviewer_name
             FROM account_requests r
             LEFT JOIN departments d ON r.department_id = d.id
             LEFT JOIN users u ON r.reviewed_by = u.id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findAll(?string $status = null): array
    {
        if ($status !== null) {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, d.name AS department_name, u.username AS reviewer_name
                 FROM account_requests r
                 LEFT JOIN departments d ON r.department_id = d.id
                 LEFT JOIN users u ON r.reviewed_by = u.id
                 WHERE r.status = ?
                 ORDER BY r.created_at DESC'
            );
            $stmt->execute([$status]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT r.*, d.name AS department_name, u.username AS reviewer_name
                 FROM account_requests r
                 LEFT JOIN departments d ON r.department_id = d.id
                 LEFT JOIN users u ON r.reviewed_by = u.id
                 ORDER BY r.created_at DESC'
            );
        }
        return $stmt->fetchAll();
    }

    public function countPending(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) AS total FROM account_requests WHERE status = 'pending'"
        );
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    public function existsPending(string $username): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM account_requests WHERE username = ? AND status = 'pending' LIMIT 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }

    public function create(string $username, ?int $departmentId, ?string $message): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO account_requests (username, department_id, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $departmentId, $message]);
        return (int) $this->pdo->lastInsertId();
    }

    public function approve(int $id, int $adminId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE account_requests 
             SET status = 'approved', reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP 
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminId, $id]);
        return $stmt->rowCount() > 0;
    }

    public function reject(int $id, int $adminId, string $reason): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE account_requests 
             SET status = 'rejected', reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, rejection_reason = ?
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$adminId, $reason, $id]);
        return $stmt->rowCount() > 0;
    }

    public function cancel(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE account_requests SET status = 'cancelled' WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
<?php

namespace App\Core;

use App\Core\Database;
use PDO;

final class ReportRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Crea un reporte. Devuelve su id.
     */
    public function create(
        int $userId,
        string $subject,
        string $description,
        string $category = 'other',
        string $priority = 'medium'
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reports (user_id, subject, description, category, priority) 
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $subject, $description, $category, $priority]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Busca un reporte por id con datos del autor y del revisor.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, 
                    u.username AS author_name,
                    u.role AS author_role,
                    d.name AS author_department,
                    rev.username AS reviewer_name
             FROM reports r
             LEFT JOIN users u ON r.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             LEFT JOIN users rev ON r.reviewed_by = rev.id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Reportes de un usuario específico.
     */
    public function findByUser(int $userId, ?string $status = null): array
    {
        if ($status !== null) {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, rev.username AS reviewer_name
                 FROM reports r
                 LEFT JOIN users rev ON r.reviewed_by = rev.id
                 WHERE r.user_id = ? AND r.status = ?
                 ORDER BY r.created_at DESC'
            );
            $stmt->execute([$userId, $status]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, rev.username AS reviewer_name
                 FROM reports r
                 LEFT JOIN users rev ON r.reviewed_by = rev.id
                 WHERE r.user_id = ?
                 ORDER BY r.created_at DESC'
            );
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Todos los reportes (para admins), con filtro opcional por estado.
     */
    public function findAll(?string $status = null, ?string $priority = null): array
    {
        $sql = 'SELECT r.*, 
                       u.username AS author_name,
                       u.role AS author_role,
                       d.name AS author_department,
                       rev.username AS reviewer_name
                FROM reports r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN users rev ON r.reviewed_by = rev.id
                WHERE 1=1';
        $params = [];

        if ($status !== null) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        if ($priority !== null) {
            $sql .= ' AND r.priority = ?';
            $params[] = $priority;
        }

        $sql .= ' ORDER BY 
                    FIELD(r.priority, "high", "medium", "low"),
                    r.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta reportes pendientes.
     */
    public function countPending(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) AS total FROM reports WHERE status = 'pending'"
        );
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    /**
     * Cuenta reportes pendientes de un usuario.
     */
    public function countPendingByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM reports WHERE user_id = ? AND status IN ('pending', 'in_review')"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    /**
     * Responde un reporte y cambia su estado.
     */
    public function respond(int $id, int $adminId, string $status, ?string $response): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reports 
             SET status = ?, admin_response = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP 
             WHERE id = ?'
        );
        $stmt->execute([$status, $response, $adminId, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Solo cambia el estado (sin respuesta).
     */
    public function updateStatus(int $id, int $adminId, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reports 
             SET status = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP 
             WHERE id = ?'
        );
        $stmt->execute([$status, $adminId, $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Borra un reporte.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM reports WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
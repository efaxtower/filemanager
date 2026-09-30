<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\ReportRepository;

final class ReportController
{
    private Auth $auth;
    private ReportRepository $reports;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->reports = new ReportRepository();
    }

    /**
     * Lista de reportes del usuario actual.
     */
    public function index(): void
    {
        $user = $this->auth->requireLogin();

        $status = $_GET['status'] ?? 'all';
        if (!in_array($status, ['all', 'pending', 'in_review', 'resolved', 'closed'], true)) {
            $status = 'all';
        }

        $reports = $this->reports->findByUser(
            (int) $user['id'],
            $status === 'all' ? null : $status
        );
        $pendingCount = $this->reports->countPendingByUser((int) $user['id']);

        require __DIR__ . '/../Views/reports/index.php';
    }

    /**
     * Muestra el formulario de nuevo reporte.
     */
    public function showCreate(): void
    {
        $user = $this->auth->requireLogin();
        require __DIR__ . '/../Views/reports/create.php';
    }

    /**
     * Procesa la creación de un reporte.
     */
    public function create(): void
    {
        $user = $this->auth->requireLogin();

        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $priority = $_POST['priority'] ?? 'medium';

        if ($subject === '' || mb_strlen($subject) > 150) {
            $_SESSION['error'] = 'El asunto es obligatorio (máx. 150 caracteres)';
            header('Location: /filemanager/reports/create');
            exit;
        }

        if ($description === '' || mb_strlen($description) < 10) {
            $_SESSION['error'] = 'La descripción debe tener al menos 10 caracteres';
            header('Location: /filemanager/reports/create');
            exit;
        }

        if (!in_array($category, ['bug', 'suggestion', 'complaint', 'other'], true)) {
            $category = 'other';
        }
        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $priority = 'medium';
        }

        try {
            $id = $this->reports->create((int) $user['id'], $subject, $description, $category, $priority);
            $_SESSION['success'] = 'Reporte enviado correctamente.';
            header('Location: /filemanager/reports/view/' . $id);
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Error al crear el reporte: ' . $e->getMessage();
            header('Location: /filemanager/reports/create');
            exit;
        }
    }

    /**
     * Ver un reporte.
     */
    public function view(int $id): void
    {
        $user = $this->auth->requireLogin();

        $report = $this->reports->findById($id);
        if ($report === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/files/404.php';
            return;
        }

        $isAuthor = (int) $report['user_id'] === (int) $user['id'];
        $isAdmin = $user['role'] === 'admin';

        if (!$isAuthor && !$isAdmin) {
            http_response_code(403);
            echo '403 - No tienes acceso a este reporte';
            exit;
        }

        require __DIR__ . '/../Views/reports/view.php';
    }
}
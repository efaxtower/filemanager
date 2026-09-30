<?php
$pageTitle = 'Admin - Reporte';
require __DIR__ . '/../partials/head.php';

$statusColors = [
    'pending' => ['bg' => 'var(--color-accent-light)', 'color' => 'var(--color-accent-dark)', 'icon' => 'fa-clock', 'label' => 'PENDIENTE'],
    'in_review' => ['bg' => '#fff4d6', 'color' => '#b8860b', 'icon' => 'fa-magnifying-glass', 'label' => 'EN REVISIÓN'],
    'resolved' => ['bg' => 'var(--color-success-bg)', 'color' => 'var(--color-success)', 'icon' => 'fa-check', 'label' => 'RESUELTO'],
    'closed' => ['bg' => 'var(--color-bg)', 'color' => 'var(--color-text-soft)', 'icon' => 'fa-lock', 'label' => 'CERRADO'],
];
$sc = $statusColors[$report['status']] ?? $statusColors['pending'];

$priorityColors = [
    'low' => ['color' => 'var(--color-text-soft)', 'label' => 'Baja'],
    'medium' => ['color' => 'var(--color-accent)', 'label' => 'Media'],
    'high' => ['color' => 'var(--color-error)', 'label' => 'Alta'],
];
$pc = $priorityColors[$report['priority']] ?? $priorityColors['medium'];

$categoryLabels = [
    'bug' => 'Bug / Error',
    'suggestion' => 'Sugerencia',
    'complaint' => 'Queja',
    'other' => 'Otro',
];
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3); min-width:0;">
                    <a href="/filemanager/admin/reports" class="btn-icon" title="Volver">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:15px;"><?= htmlspecialchars($report['subject']) ?></strong>
                </div>
                <div class="topbar__actions">
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert--error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert--success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div style="max-width:900px;">
                    <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid <?= $sc['color'] ?>; border-radius:var(--radius-md); padding:var(--space-5); margin-bottom:var(--space-4); box-shadow:var(--shadow-neu);">
                        <div style="display:flex; justify-content:space-between; align-items:start; gap:var(--space-3); margin-bottom:var(--space-4); flex-wrap:wrap;">
                            <div>
                                <h1 style="font-size:20px; font-weight:600; color:var(--color-text); margin-bottom:var(--space-2);">
                                    <?= htmlspecialchars($report['subject']) ?>
                                </h1>
                                <p style="font-size:12px; color:var(--color-text-soft);">
                                    <i class="fa-solid fa-user"></i> <?= htmlspecialchars($report['author_name']) ?>
                                    <?php if ($report['author_role'] === 'admin'): ?>
                                        <span style="background:var(--color-primary-light); color:var(--color-primary-dark); padding:1px 6px; border-radius:var(--radius-full); font-size:9px; font-weight:700; margin-left:4px;">ADMIN</span>
                                    <?php endif; ?>
                                    <?php if (!empty($report['author_department'])): ?>
                                        · <?= htmlspecialchars($report['author_department']) ?>
                                    <?php endif; ?>
                                    &nbsp;·&nbsp;
                                    <i class="fa-solid fa-calendar"></i> <?= date('d M Y H:i', strtotime($report['created_at'])) ?>
                                </p>
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:var(--space-2);">
                                <span style="background:<?= $sc['bg'] ?>; color:<?= $sc['color'] ?>; padding:4px 12px; border-radius:var(--radius-full); font-size:11px; font-weight:700;">
                                    <i class="fa-solid <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                                </span>
                                <span style="font-size:11px; color:<?= $pc['color'] ?>; font-weight:600;">
                                    <i class="fa-solid fa-circle" style="font-size:6px;"></i> Prioridad <?= $pc['label'] ?>
                                </span>
                            </div>
                        </div>

                        <div style="display:flex; gap:var(--space-4); margin-bottom:var(--space-4); font-size:12px; color:var(--color-text-soft);">
                            <span><i class="fa-solid fa-tag"></i> <?= $categoryLabels[$report['category']] ?? 'Otro' ?></span>
                        </div>

                        <div style="background:var(--color-bg); padding:var(--space-4); border-radius:var(--radius-sm); font-size:14px; color:var(--color-text); line-height:1.7;">
                            <?= nl2br(htmlspecialchars($report['description'])) ?>
                        </div>
                    </div>

                    <form method="POST" action="/filemanager/admin/reports/respond" style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid var(--color-accent); border-radius:var(--radius-md); padding:var(--space-5); box-shadow:var(--shadow-neu);">
                        <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">

                        <h3 style="font-size:14px; font-weight:600; color:var(--color-text-soft); text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-4);">
                            <i class="fa-solid fa-reply"></i> Gestionar reporte
                        </h3>

                        <div style="display:flex; flex-direction:column; gap:var(--space-4);">
                            <div>
                                <label class="modal__label">Estado</label>
                                <select name="status" class="modal__input">
                                    <option value="pending" <?= $report['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="in_review" <?= $report['status'] === 'in_review' ? 'selected' : '' ?>>En revisión</option>
                                    <option value="resolved" <?= $report['status'] === 'resolved' ? 'selected' : '' ?>>Resuelto</option>
                                    <option value="closed" <?= $report['status'] === 'closed' ? 'selected' : '' ?>>Cerrado</option>
                                </select>
                            </div>

                            <div>
                                <label class="modal__label">Respuesta al usuario (opcional)</label>
                                <textarea name="admin_response" class="modal__input" rows="5" placeholder="Escribe una respuesta para el autor del reporte..."><?= htmlspecialchars($report['admin_response'] ?? '') ?></textarea>
                            </div>

                            <div style="display:flex; justify-content:flex-end; gap:var(--space-2);">
                                <a href="/filemanager/admin/reports" class="btn btn--ghost">Cancelar</a>
                                <button type="submit" class="btn btn--primary">
                                    <i class="fa-solid fa-check"></i> Guardar cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
<?php
$pageTitle = 'Admin - Reportes';
require __DIR__ . '/../partials/head.php';

$statusColors = [
    'pending' => ['bg' => 'var(--color-accent-light)', 'color' => 'var(--color-accent-dark)', 'icon' => 'fa-clock', 'label' => 'PENDIENTE'],
    'in_review' => ['bg' => '#fff4d6', 'color' => '#b8860b', 'icon' => 'fa-magnifying-glass', 'label' => 'EN REVISIÓN'],
    'resolved' => ['bg' => 'var(--color-success-bg)', 'color' => 'var(--color-success)', 'icon' => 'fa-check', 'label' => 'RESUELTO'],
    'closed' => ['bg' => 'var(--color-bg)', 'color' => 'var(--color-text-soft)', 'icon' => 'fa-lock', 'label' => 'CERRADO'],
];
$priorityColors = [
    'low' => ['color' => 'var(--color-text-soft)', 'label' => 'Baja'],
    'medium' => ['color' => 'var(--color-accent)', 'label' => 'Media'],
    'high' => ['color' => 'var(--color-error)', 'label' => 'Alta'],
];
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-clipboard-list" style="color:var(--color-accent);"></i> Reportes</strong>
                    <?php if ($pendingCount > 0): ?>
                        <span style="background:var(--color-error); color:white; font-size:11px; padding:3px 10px; border-radius:var(--radius-full); font-weight:700;">
                            <?= (int) $pendingCount ?> pendiente<?= $pendingCount === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
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

                <div class="actions-bar" style="flex-direction:column; align-items:stretch; gap:var(--space-2);">
                    <div style="display:flex; gap:var(--space-2); flex-wrap:wrap;">
                        <a href="/filemanager/admin/reports?status=all&priority=<?= htmlspecialchars($priority) ?>" class="btn <?= $status === 'all' ? 'btn--primary' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-list"></i> Todos
                        </a>
                        <a href="/filemanager/admin/reports?status=pending&priority=<?= htmlspecialchars($priority) ?>" class="btn <?= $status === 'pending' ? 'btn--primary' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-clock"></i> Pendientes
                        </a>
                        <a href="/filemanager/admin/reports?status=in_review&priority=<?= htmlspecialchars($priority) ?>" class="btn <?= $status === 'in_review' ? 'btn--primary' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-magnifying-glass"></i> En revisión
                        </a>
                        <a href="/filemanager/admin/reports?status=resolved&priority=<?= htmlspecialchars($priority) ?>" class="btn <?= $status === 'resolved' ? 'btn--primary' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-check"></i> Resueltos
                        </a>
                        <a href="/filemanager/admin/reports?status=closed&priority=<?= htmlspecialchars($priority) ?>" class="btn <?= $status === 'closed' ? 'btn--primary' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-lock"></i> Cerrados
                        </a>
                    </div>
                    <div style="display:flex; gap:var(--space-2); flex-wrap:wrap;">
                        <a href="/filemanager/admin/reports?status=<?= htmlspecialchars($status) ?>&priority=all" class="btn <?= $priority === 'all' ? 'btn--accent' : 'btn--ghost' ?>">
                            Todas las prioridades
                        </a>
                        <a href="/filemanager/admin/reports?status=<?= htmlspecialchars($status) ?>&priority=high" class="btn <?= $priority === 'high' ? 'btn--accent' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-circle" style="font-size:8px; color:var(--color-error);"></i> Alta
                        </a>
                        <a href="/filemanager/admin/reports?status=<?= htmlspecialchars($status) ?>&priority=medium" class="btn <?= $priority === 'medium' ? 'btn--accent' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-circle" style="font-size:8px; color:var(--color-accent);"></i> Media
                        </a>
                        <a href="/filemanager/admin/reports?status=<?= htmlspecialchars($status) ?>&priority=low" class="btn <?= $priority === 'low' ? 'btn--accent' : 'btn--ghost' ?>">
                            <i class="fa-solid fa-circle" style="font-size:8px; color:var(--color-text-soft);"></i> Baja
                        </a>
                    </div>
                </div>

                <?php if (empty($reports)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-clipboard"></i></div>
                        <h2>Sin reportes</h2>
                        <p>No hay reportes en este estado</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:var(--space-3);">
                        <?php foreach ($reports as $r): ?>
                            <?php
                            $sc = $statusColors[$r['status']] ?? $statusColors['pending'];
                            $pc = $priorityColors[$r['priority']] ?? $priorityColors['medium'];
                            ?>
                            <a href="/filemanager/admin/reports/view/<?= (int) $r['id'] ?>" style="text-decoration:none; color:inherit;">
                                <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid <?= $sc['color'] ?>; border-radius:var(--radius-md); padding:var(--space-4); box-shadow:var(--shadow-neu); transition:all 0.2s;">
                                    <div style="display:flex; justify-content:space-between; align-items:start; gap:var(--space-3); flex-wrap:wrap;">
                                        <div style="flex:1; min-width:200px;">
                                            <div style="display:flex; align-items:center; gap:var(--space-2); margin-bottom:var(--space-2); flex-wrap:wrap;">
                                                <h3 style="font-size:15px; font-weight:600; color:var(--color-text);">
                                                    <?= htmlspecialchars($r['subject']) ?>
                                                </h3>
                                                <span style="background:<?= $sc['bg'] ?>; color:<?= $sc['color'] ?>; padding:2px 8px; border-radius:var(--radius-full); font-size:10px; font-weight:700;">
                                                    <i class="fa-solid <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                                                </span>
                                                <span style="color:<?= $pc['color'] ?>; font-size:10px; font-weight:700;">
                                                    <i class="fa-solid fa-circle" style="font-size:6px;"></i> <?= strtoupper($pc['label']) ?>
                                                </span>
                                            </div>
                                            <p style="font-size:12px; color:var(--color-text-soft);">
                                                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($r['author_name']) ?>
                                                <?php if ($r['author_role'] === 'admin'): ?>
                                                    <span style="background:var(--color-primary-light); color:var(--color-primary-dark); padding:1px 6px; border-radius:var(--radius-full); font-size:9px; font-weight:700; margin-left:4px;">ADMIN</span>
                                                <?php endif; ?>
                                                <?php if (!empty($r['author_department'])): ?>
                                                    · <?= htmlspecialchars($r['author_department']) ?>
                                                <?php endif; ?>
                                                &nbsp;·&nbsp;
                                                <i class="fa-solid fa-calendar"></i> <?= date('d M Y H:i', strtotime($r['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
<?php
$pageTitle = 'Compartidos';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-folder-tree" style="color:var(--color-accent);"></i> Compartidos</strong>
                </div>
                <div class="topbar__actions">
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content">
                <?php if (empty($folders)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-folder-tree"></i></div>
                        <h2>Sin carpetas compartidas</h2>
                        <p>No hay carpetas compartidas para tu departamento</p>
                    </div>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap:var(--space-3);">
                        <?php foreach ($folders as $f): ?>
                            <a href="/filemanager/shared/view/<?= (int) $f['id'] ?>" style="text-decoration:none; color:inherit;">
                                <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid <?= $f['is_public'] ? 'var(--color-primary)' : 'var(--color-accent)' ?>; border-radius:var(--radius-md); padding:var(--space-4); box-shadow:var(--shadow-neu); transition:all 0.2s;">
                                    <h3 style="font-size:15px; font-weight:600; color:var(--color-text); word-break:break-word;">
                                        <i class="fa-solid fa-folder" style="color:var(--color-accent);"></i>
                                        <?= htmlspecialchars($f['name']) ?>
                                    </h3>
                                    <p style="font-size:12px; color:var(--color-text-soft); margin-top:var(--space-2);">
                                        <?php if ($f['is_public']): ?>
                                            <span style="background:var(--color-primary-light); color:var(--color-primary-dark); padding:2px 8px; border-radius:var(--radius-full); font-size:10px; font-weight:700;">
                                                <i class="fa-solid fa-globe"></i> PÚBLICA
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($f['department_name'])): ?>
                                            <span style="background:var(--color-accent-light); color:var(--color-accent-dark); padding:2px 8px; border-radius:var(--radius-full); font-size:10px; font-weight:700; margin-left:4px;">
                                                <i class="fa-solid fa-building"></i> <?= htmlspecialchars($f['department_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
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
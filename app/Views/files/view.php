<?php
$pageTitle = htmlspecialchars($resolved->name);
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="display:flex; align-items:center; gap:var(--space-3); flex:1; min-width:0;">
                    <a href="/filemanager/files<?= htmlspecialchars(dirname($resolved->logicalPath) === '/' ? '' : dirname($resolved->logicalPath)) ?>" class="btn-icon" title="Volver">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:15px;"><?= htmlspecialchars($resolved->name) ?></strong>
                    <span class="quota"><i class="fa-solid fa-weight-hanging"></i> <?= number_format($size / 1024, 2) ?> KB</span>
                </div>
                <div class="topbar__actions">
                    <a href="/filemanager/files/download<?= htmlspecialchars($resolved->logicalPath) ?>" class="btn btn--accent">
                        <i class="fa-solid fa-download"></i> Descargar
                    </a>
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content" style="padding: 0;">
                <?php if (str_starts_with($mime, 'image/')): ?>
                    <div style="display:flex; align-items:center; justify-content:center; height:100%; padding:var(--space-4); background:var(--color-bg);">
                        <img src="data:<?= htmlspecialchars($mime) ?>;base64,<?= base64_encode($content) ?>"
                             alt="<?= htmlspecialchars($resolved->name) ?>"
                             style="max-width:100%; max-height:100%; border-radius:var(--radius-md); box-shadow:var(--shadow-lg);">
                    </div>
                <?php elseif (str_starts_with($mime, 'text/') || $mime === 'application/json' || $mime === 'application/xml'): ?>
                    <pre style="padding:var(--space-5); margin:0; height:100%; overflow:auto; font-family:'Consolas','Monaco',monospace; font-size:14px; line-height:1.7; background:var(--color-surface); color:var(--color-text);"><?= htmlspecialchars($content) ?></pre>
                <?php elseif ($mime === 'application/pdf'): ?>
                    <iframe src="data:application/pdf;base64,<?= base64_encode($content) ?>"
                            style="width:100%; height:100%; border:none;"></iframe>
                <?php else: ?>
                    <div style="display:flex; align-items:center; justify-content:center; height:100%; padding:var(--space-6);">
                        <div style="background:var(--glass-bg); backdrop-filter:blur(var(--glass-blur)); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:var(--space-6); max-width:500px; text-align:center; box-shadow:var(--shadow-lg);">
                            <div style="width:120px; height:120px; margin:0 auto var(--space-4); background:var(--gradient-mixed); clip-path:polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%); display:flex; align-items:center; justify-content:center; font-size:56px; color:white; animation:float 4s ease-in-out infinite;">
                                <i class="fa-solid fa-file"></i>
                            </div>
                            <h2 style="color:var(--color-text); font-size:20px; margin-bottom:var(--space-2); font-weight:600; word-break:break-word;"><?= htmlspecialchars($resolved->name) ?></h2>
                            <p style="color:var(--color-text-soft); font-size:12px; margin-bottom:var(--space-1);">
                                <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($mime) ?>
                            </p>
                            <p style="color:var(--color-text-soft); font-size:12px; margin-bottom:var(--space-4);">
                                <i class="fa-solid fa-weight-hanging"></i> <?= number_format($size / 1024, 2) ?> KB
                            </p>
                            <p style="color:var(--color-text-muted); margin-bottom:var(--space-4);">
                                Este tipo de archivo no se puede previsualizar.
                            </p>
                            <a href="/filemanager/files/download<?= htmlspecialchars($resolved->logicalPath) ?>" class="btn btn--accent">
                                <i class="fa-solid fa-download"></i> Descargar archivo
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
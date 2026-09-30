<?php
$pageTitle = 'Buscar: ' . htmlspecialchars($query);
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <form method="GET" action="/filemanager/search" style="flex:1; max-width:720px; display:flex;">
                    <input type="text" name="q" class="topbar__search"
                           placeholder="Buscar en FileManager..."
                           value="<?= htmlspecialchars($query) ?>"
                           autofocus>
                </form>
                <div class="topbar__actions">
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content">
                <div class="content__header">
                    <div class="breadcrumb">
                        <i class="fa-solid fa-magnifying-glass" style="margin-right:var(--space-2); color:var(--color-accent);"></i>
                        Resultados: <span class="current"><?= htmlspecialchars($query) ?></span>
                    </div>
                    <div class="quota">
                        <i class="fa-solid fa-list"></i>
                        <?= count($results) ?> resultado<?= count($results) === 1 ? '' : 's' ?>
                    </div>
                </div>

                <?php if (empty($results)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h2>Sin resultados</h2>
                        <p>No se encontraron archivos ni carpetas con "<?= htmlspecialchars($query) ?>"</p>
                        <a href="/filemanager/" class="btn btn--primary" style="margin-top:var(--space-4);">
                            <i class="fa-solid fa-arrow-left"></i> Volver al inicio
                        </a>
                    </div>
                <?php else: ?>
                    <?php
                    $getTypeClass = function($item) {
                        if ($item['type'] === 'folder') return 'folder';
                        $mime = $item['mime_type'] ?? '';
                        if (str_starts_with($mime, 'image/')) return 'image';
                        if ($mime === 'application/pdf') return 'pdf';
                        if (str_starts_with($mime, 'text/') || $mime === 'application/json') return 'text';
                        if (str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar')) return 'archive';
                        return 'other';
                    };
                    $getIconClass = function($item) {
                        if ($item['type'] === 'folder') return 'fa-folder';
                        $mime = $item['mime_type'] ?? '';
                        if (str_starts_with($mime, 'image/')) return 'fa-file-image';
                        if ($mime === 'application/pdf') return 'fa-file-pdf';
                        if (str_starts_with($mime, 'text/') || $mime === 'application/json') return 'fa-file-lines';
                        if (str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar')) return 'fa-file-zipper';
                        return 'fa-file';
                    };
                    $formatDate = function($date) { return date('d M Y', strtotime($date)); };
                    ?>
                    <div class="file-grid">
                        <?php foreach ($results as $item):
                            $typeClass = $getTypeClass($item);
                            $iconClass = $getIconClass($item);
                            $isImage = $typeClass === 'image';
                        ?>
                            <div class="file-card file-card--<?= $typeClass ?>">
                                <?php if ($item['type'] === 'folder'): ?>
                                    <a href="/filemanager/files<?= htmlspecialchars($item['full_path']) ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                        <div class="file-card__preview <?= $typeClass ?>"><i class="fa-solid <?= $iconClass ?>"></i></div>
                                        <div class="file-card__name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="file-card__info">
                                            <div class="file-card__info-row" title="<?= htmlspecialchars($item['full_path']) ?>"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars(dirname($item['full_path'])) ?></div>
                                            <div class="file-card__info-row"><i class="fa-solid fa-calendar"></i> <?= $formatDate($item['created_at']) ?></div>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <a href="/filemanager/files/view<?= htmlspecialchars($item['full_path']) ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                        <div class="file-card__preview <?= $typeClass ?>">
                                            <?php if ($isImage): ?>
                                                <img src="/filemanager/files/download<?= htmlspecialchars($item['full_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                            <?php else: ?>
                                                <i class="fa-solid <?= $iconClass ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="file-card__name"><?= htmlspecialchars($item['name']) ?></div>
                                        <div class="file-card__info">
                                            <div class="file-card__info-row" title="<?= htmlspecialchars($item['full_path']) ?>"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars(dirname($item['full_path'])) ?></div>
                                            <div class="file-card__info-row"><i class="fa-solid fa-weight-hanging"></i> <?= number_format($item['size_bytes'] / 1024, 2) ?> KB</div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
<?php
$pageTitle = 'Explorador';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <form method="GET" action="/filemanager/search" style="flex:1; max-width:720px; display:flex;">
                    <input type="text" name="q" class="topbar__search" placeholder="Buscar en FileManager...">
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
                        <a href="/filemanager/">Mi unidad</a>
                        <?php
                        $parts = array_filter(explode('/', $resolved->logicalPath));
                        $accumulated = '';
                        foreach ($parts as $part):
                            $accumulated .= '/' . $part;
                        ?>
                            <span class="sep">/</span>
                            <a href="/filemanager/files<?= htmlspecialchars($accumulated) ?>">
                                <?= htmlspecialchars($part) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="quota">
                        <i class="fa-solid fa-database"></i>
                        <?= number_format($usedBytes / 1024 / 1024, 2) ?> MB /
                        <?= number_format($user['quota_bytes'] / 1024 / 1024 / 1024, 2) ?> GB
                    </div>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert--error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert--success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="actions-bar">
                    <form method="POST" action="/filemanager/files/folder" class="form-inline">
                        <input type="hidden" name="path" value="<?= htmlspecialchars($resolved->logicalPath) ?>">
                        <input type="text" name="name" id="new-folder-name" class="input" placeholder="Nombre de carpeta" required>
                        <button type="submit" class="btn btn--primary">
                            <i class="fa-solid fa-folder-plus"></i> Crear carpeta
                        </button>
                    </form>

                    <form method="POST" action="/filemanager/files/upload" enctype="multipart/form-data" class="form-inline">
                        <input type="hidden" name="path" value="<?= htmlspecialchars($resolved->logicalPath) ?>">
                        <input type="file" name="file" id="file-input" style="display:none;" onchange="this.form.submit()">
                        <button type="button" class="btn btn--ghost" onclick="document.getElementById('file-input').click();">
                            <i class="fa-solid fa-file-arrow-up"></i> Subir archivo
                        </button>
                    </form>

                    <div class="spacer"></div>

                    <button type="button" class="btn-icon" onclick="toggleView('grid')" title="Vista cuadrícula">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button" class="btn-icon" onclick="toggleView('list')" title="Vista lista">
                        <i class="fa-solid fa-list"></i>
                    </button>
                </div>

                <?php if (empty($children)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-folder-open"></i></div>
                        <h2>Carpeta vacía</h2>
                        <p>Sube un archivo o crea una carpeta</p>
                    </div>
                <?php else: ?>
                    <?php
                    $getTypeClass = function($child) {
                        if ($child['type'] === 'folder') return 'folder';
                        $mime = $child['mime_type'] ?? '';
                        if (str_starts_with($mime, 'image/')) return 'image';
                        if ($mime === 'application/pdf') return 'pdf';
                        if (str_starts_with($mime, 'text/') || $mime === 'application/json') return 'text';
                        if (str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar')) return 'archive';
                        return 'other';
                    };
                    $getIconClass = function($child) {
                        if ($child['type'] === 'folder') return 'fa-folder';
                        $mime = $child['mime_type'] ?? '';
                        if (str_starts_with($mime, 'image/')) return 'fa-file-image';
                        if ($mime === 'application/pdf') return 'fa-file-pdf';
                        if (str_starts_with($mime, 'text/') || $mime === 'application/json') return 'fa-file-lines';
                        if (str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar')) return 'fa-file-zipper';
                        return 'fa-file';
                    };
                    $formatDate = function($date) { return date('d M Y', strtotime($date)); };
                    ?>

                    <div class="file-grid">
                        <?php foreach ($children as $child):
                            $typeClass = $getTypeClass($child);
                            $iconClass = $getIconClass($child);
                            $isImage = $typeClass === 'image';
                            $fullPath = $resolved->logicalPath === '/' ? '' : $resolved->logicalPath;
                        ?>
                            <div class="file-card file-card--<?= $typeClass ?>">
                                <div class="file-card__actions">
                                    <button type="button" class="file-card__action" onclick="openRenameModal('<?= htmlspecialchars($resolved->logicalPath, ENT_QUOTES) ?>', '<?= htmlspecialchars($child['name'], ENT_QUOTES) ?>')" title="Renombrar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="/filemanager/files/delete" style="display:inline;">
                                        <input type="hidden" name="path" value="<?= htmlspecialchars($resolved->logicalPath) ?>">
                                        <input type="hidden" name="name" value="<?= htmlspecialchars($child['name']) ?>">
                                        <button type="submit" class="file-card__action file-card__action--delete" onclick="return confirm('¿Borrar?')" title="Borrar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <?php if ($child['type'] === 'folder'): ?>
                                    <a href="/filemanager/files<?= htmlspecialchars($fullPath) ?>/<?= htmlspecialchars($child['name']) ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                        <div class="file-card__preview <?= $typeClass ?>"><i class="fa-solid <?= $iconClass ?>"></i></div>
                                        <div class="file-card__name"><?= htmlspecialchars($child['name']) ?></div>
                                        <div class="file-card__info">
                                            <div class="file-card__info-row"><i class="fa-solid fa-folder"></i> Carpeta</div>
                                            <div class="file-card__info-row"><i class="fa-solid fa-calendar"></i> <?= $formatDate($child['created_at']) ?></div>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <a href="/filemanager/files/view<?= htmlspecialchars($fullPath) ?>/<?= htmlspecialchars($child['name']) ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                        <div class="file-card__preview <?= $typeClass ?>">
                                            <?php if ($isImage): ?>
                                                <img src="/filemanager/files/download<?= htmlspecialchars($fullPath) ?>/<?= htmlspecialchars($child['name']) ?>" alt="<?= htmlspecialchars($child['name']) ?>" loading="lazy">
                                            <?php else: ?>
                                                <i class="fa-solid <?= $iconClass ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="file-card__name"><?= htmlspecialchars($child['name']) ?></div>
                                        <div class="file-card__info">
                                            <div class="file-card__info-row"><i class="fa-solid fa-weight-hanging"></i> <?= number_format($child['size_bytes'] / 1024, 2) ?> KB</div>
                                            <div class="file-card__info-row"><i class="fa-solid fa-calendar"></i> <?= $formatDate($child['created_at']) ?></div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="file-list" style="display:none;">
                        <?php foreach ($children as $child):
                            $typeClass = $getTypeClass($child);
                            $iconClass = $getIconClass($child);
                            $fullPath = $resolved->logicalPath === '/' ? '' : $resolved->logicalPath;
                        ?>
                            <div class="file-row file-row--<?= $typeClass ?>">
                                <div class="file-row__icon <?= $typeClass ?>"><i class="fa-solid <?= $iconClass ?>"></i></div>
                                <?php if ($child['type'] === 'folder'): ?>
                                    <a href="/filemanager/files<?= htmlspecialchars($fullPath) ?>/<?= htmlspecialchars($child['name']) ?>" style="text-decoration:none;">
                                        <div class="file-row__name"><?= htmlspecialchars($child['name']) ?></div>
                                    </a>
                                <?php else: ?>
                                    <a href="/filemanager/files/view<?= htmlspecialchars($fullPath) ?>/<?= htmlspecialchars($child['name']) ?>" style="text-decoration:none;">
                                        <div class="file-row__name"><?= htmlspecialchars($child['name']) ?></div>
                                    </a>
                                <?php endif; ?>
                                <div class="file-row__meta"><?= $child['type'] === 'folder' ? 'Carpeta' : number_format($child['size_bytes'] / 1024, 2) . ' KB' ?></div>
                                <div class="file-row__meta"><?= $formatDate($child['created_at']) ?></div>
                                <div class="file-row__actions">
                                    <button type="button" class="file-row__action" onclick="openRenameModal('<?= htmlspecialchars($resolved->logicalPath, ENT_QUOTES) ?>', '<?= htmlspecialchars($child['name'], ENT_QUOTES) ?>')" title="Renombrar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="/filemanager/files/delete" style="display:inline;">
                                        <input type="hidden" name="path" value="<?= htmlspecialchars($resolved->logicalPath) ?>">
                                        <input type="hidden" name="name" value="<?= htmlspecialchars($child['name']) ?>">
                                        <button type="submit" class="file-row__action file-row__action--delete" onclick="return confirm('¿Borrar?')" title="Borrar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="rename-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-pen"></i></div>
                <div class="modal__title">Renombrar</div>
            </div>
            <form method="POST" action="/filemanager/files/rename" class="modal__form">
                <div>
                    <label class="modal__label">Nuevo nombre</label>
                    <input type="text" name="new_name" id="rename-input" class="modal__input" required autocomplete="off">
                </div>
                <input type="hidden" name="path" id="rename-path">
                <input type="hidden" name="old_name" id="rename-old">
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeRenameModal()">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        (function() {
            const view = localStorage.getItem('view') || 'grid';
            if (view === 'list') {
                const grid = document.querySelector('.file-grid');
                const list = document.querySelector('.file-list');
                if (grid && list) {
                    grid.style.display = 'none';
                    list.style.display = 'flex';
                }
            }
        })();
    </script>
</body>
</html>
<?php
$pageTitle = htmlspecialchars($folder['name']);
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3); min-width:0;">
                    <a href="/filemanager/shared<?= $folder['parent_id'] ? '/view/' . (int) $folder['parent_id'] : '' ?>" class="btn-icon" title="Volver">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:15px;">
                        <?= htmlspecialchars($folder['name']) ?>
                    </strong>
                    <?php if ($folder['is_public']): ?>
                        <span style="background:var(--color-primary-light); color:var(--color-primary-dark); padding:2px 8px; border-radius:var(--radius-full); font-size:10px; font-weight:700;">
                            <i class="fa-solid fa-globe"></i> PÚBLICA
                        </span>
                    <?php endif; ?>
                </div>
                <div class="topbar__actions">
                    <?php if ($canWrite): ?>
                        <button class="btn btn--primary" onclick="openModal('create-folder-modal')">
                            <i class="fa-solid fa-folder-plus"></i> Subcarpeta
                        </button>
                        <button class="btn btn--accent" onclick="document.getElementById('file-input').click();">
                            <i class="fa-solid fa-file-arrow-up"></i> Subir archivo
                        </button>
                        <form method="POST" action="/filemanager/shared/upload" enctype="multipart/form-data" style="display:none;">
                            <input type="hidden" name="folder_id" value="<?= (int) $folder['id'] ?>">
                            <input type="file" name="file" id="file-input" onchange="this.form.submit()">
                        </form>
                    <?php endif; ?>
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

                <?php if (empty($children) && empty($files)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-folder-open"></i></div>
                        <h2>Carpeta vacía</h2>
                        <p>No hay subcarpetas ni archivos aquí</p>
                    </div>
                <?php else: ?>
                    <div class="file-grid">
                        <?php foreach ($children as $c): ?>
                            <?php
                            $canDeleteChild = $this->shared->canUserAccess((int) $c['id'], (int) $user['id'], $user['department_id'] ? (int) $user['department_id'] : null, $user['role'] === 'admin', 'delete');
                            $canWriteChild = $this->shared->canUserAccess((int) $c['id'], (int) $user['id'], $user['department_id'] ? (int) $user['department_id'] : null, $user['role'] === 'admin', 'write');
                            ?>
                            <div class="file-card file-card--folder">
                                <div class="file-card__actions">
                                    <?php if ($canWriteChild): ?>
                                        <button type="button" class="file-card__action" onclick="openMoveFolderModal(<?= (int) $c['id'] ?>, '<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>')" title="Mover">
                                            <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canDeleteChild): ?>
                                        <form method="POST" action="/filemanager/shared/delete-folder" style="display:inline;" onsubmit="return confirm('¿Eliminar esta carpeta y todo su contenido?');">
                                            <input type="hidden" name="folder_id" value="<?= (int) $c['id'] ?>">
                                            <button type="submit" class="file-card__action file-card__action--delete" title="Borrar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <a href="/filemanager/shared/view/<?= (int) $c['id'] ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                    <div class="file-card__preview folder">
                                        <i class="fa-solid fa-folder"></i>
                                    </div>
                                    <div class="file-card__name"><?= htmlspecialchars($c['name']) ?></div>
                                    <div class="file-card__info">
                                        <div class="file-card__info-row"><i class="fa-solid fa-folder"></i> Carpeta</div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($files as $f): ?>
                            <?php
                            $mime = $f['mime_type'] ?? '';
                            $iconClass = 'fa-file';
                            $typeClass = 'other';
                            if (str_starts_with($mime, 'image/')) { $iconClass = 'fa-file-image'; $typeClass = 'image'; }
                            elseif ($mime === 'application/pdf') { $iconClass = 'fa-file-pdf'; $typeClass = 'pdf'; }
                            elseif (str_starts_with($mime, 'text/') || $mime === 'application/json') { $iconClass = 'fa-file-lines'; $typeClass = 'text'; }
                            elseif (str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'tar')) { $iconClass = 'fa-file-zipper'; $typeClass = 'archive'; }

                            $canDeleteFile = $this->shared->canUserAccess((int) $f['folder_id'], (int) $user['id'], $user['department_id'] ? (int) $user['department_id'] : null, $user['role'] === 'admin', 'delete');
                            $canWriteFile = $this->shared->canUserAccess((int) $f['folder_id'], (int) $user['id'], $user['department_id'] ? (int) $user['department_id'] : null, $user['role'] === 'admin', 'write');
                            ?>
                            <div class="file-card file-card--<?= $typeClass ?>">
                                <div class="file-card__actions">
                                    <?php if ($canWriteFile): ?>
                                        <button type="button" class="file-card__action" onclick="openRenameSharedModal(<?= (int) $f['id'] ?>, '<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>')" title="Renombrar">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="file-card__action" onclick="openMoveFileModal(<?= (int) $f['id'] ?>, '<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>')" title="Mover">
                                            <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canDeleteFile): ?>
                                        <form method="POST" action="/filemanager/shared/delete-file" style="display:inline;" onsubmit="return confirm('¿Borrar este archivo?');">
                                            <input type="hidden" name="file_id" value="<?= (int) $f['id'] ?>">
                                            <button type="submit" class="file-card__action file-card__action--delete" title="Borrar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <a href="/filemanager/shared/view-file/<?= (int) $f['id'] ?>" style="text-decoration:none; display:flex; flex-direction:column; gap:var(--space-2); flex:1;">
                                    <div class="file-card__preview <?= $typeClass ?>">
                                        <i class="fa-solid <?= $iconClass ?>"></i>
                                    </div>
                                    <div class="file-card__name"><?= htmlspecialchars($f['name']) ?></div>
                                    <div class="file-card__info">
                                        <div class="file-card__info-row"><i class="fa-solid fa-weight-hanging"></i> <?= number_format($f['size_bytes'] / 1024, 2) ?> KB</div>
                                        <div class="file-card__info-row"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($f['uploader_name']) ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal crear subcarpeta -->
    <div class="modal-overlay" id="create-folder-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-folder-plus"></i></div>
                <div class="modal__title">Nueva subcarpeta</div>
            </div>
            <form method="POST" action="/filemanager/shared/folder/create" class="modal__form">
                <input type="hidden" name="parent_id" value="<?= (int) $folder['id'] ?>">
                <div>
                    <label class="modal__label">Nombre</label>
                    <input type="text" name="name" class="modal__input" required autocomplete="off">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('create-folder-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Crear</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal renombrar archivo -->
    <div class="modal-overlay" id="rename-shared-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-pen"></i></div>
                <div class="modal__title">Renombrar archivo</div>
            </div>
            <form method="POST" action="/filemanager/shared/rename-file" class="modal__form">
                <input type="hidden" name="file_id" id="rename-shared-id">
                <div>
                    <label class="modal__label">Nuevo nombre</label>
                    <input type="text" name="new_name" id="rename-shared-input" class="modal__input" required autocomplete="off">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('rename-shared-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal mover archivo -->
    <div class="modal-overlay" id="move-file-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-arrows-up-down-left-right"></i></div>
                <div class="modal__title">Mover archivo: <span id="move-file-name"></span></div>
            </div>
            <form method="POST" action="/filemanager/shared/move-file" class="modal__form">
                <input type="hidden" name="file_id" id="move-file-id">
                <div>
                    <label class="modal__label">Carpeta destino</label>
                    <select name="target_folder_id" id="move-file-target" class="modal__input" required>
                        <option value="">Cargando carpetas...</option>
                    </select>
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('move-file-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Mover</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal mover carpeta -->
    <div class="modal-overlay" id="move-folder-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-arrows-up-down-left-right"></i></div>
                <div class="modal__title">Mover carpeta: <span id="move-folder-name"></span></div>
            </div>
            <form method="POST" action="/filemanager/shared/move-folder" class="modal__form">
                <input type="hidden" name="folder_id" id="move-folder-id">
                <div>
                    <label class="modal__label">Carpeta destino</label>
                    <select name="target_parent_id" id="move-folder-target" class="modal__input" required>
                        <option value="">Cargando carpetas...</option>
                    </select>
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('move-folder-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Mover</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }

        function openRenameSharedModal(id, name) {
            document.getElementById('rename-shared-id').value = id;
            document.getElementById('rename-shared-input').value = name;
            openModal('rename-shared-modal');
        }

 async function loadTargetFolders(excludeId = null) {
    try {
        const res = await fetch('/filemanager/shared/target-folders');
        const data = await res.json();
        const folders = data.folders || [];

        let html = '<option value="">Selecciona una carpeta...</option>';  // ← placeholder
        folders.forEach(f => {
            if (excludeId !== null && parseInt(f.id) === parseInt(excludeId)) return;
            const indent = '\u00A0\u00A0\u00A0\u00A0'.repeat(f.level);
            html += `<option value="${f.id}">${indent}${f.path.replace('/ ', '')}</option>`;
        });

        return html;
    } catch (e) {
        return '<option value="">Error cargando carpetas</option>';
    }
}

        async function openMoveFileModal(id, name) {
            document.getElementById('move-file-id').value = id;
            document.getElementById('move-file-name').textContent = name;
            const select = document.getElementById('move-file-target');
            select.innerHTML = '<option value="">Cargando...</option>';
            openModal('move-file-modal');
            const html = await loadTargetFolders();
            select.innerHTML = html;
        }

        async function openMoveFolderModal(id, name) {
            document.getElementById('move-folder-id').value = id;
            document.getElementById('move-folder-name').textContent = name;
            const select = document.getElementById('move-folder-target');
            select.innerHTML = '<option value="">Cargando...</option>';
            openModal('move-folder-modal');
            const html = await loadTargetFolders(id);
            select.innerHTML = html;
        }

        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>
</body>
</html>
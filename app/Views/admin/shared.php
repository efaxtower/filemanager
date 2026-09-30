<?php
$pageTitle = 'Admin - Carpetas compartidas';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-folder-tree" style="color:var(--color-accent);"></i> Carpetas compartidas</strong>
                </div>
                <div class="topbar__actions">
                    <button class="btn btn--primary" onclick="openModal('create-folder-modal')">
                        <i class="fa-solid fa-plus"></i> Nueva carpeta
                    </button>
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

                <?php if (empty($folders)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-folder-tree"></i></div>
                        <h2>Sin carpetas compartidas</h2>
                        <p>Crea la primera carpeta raíz para empezar</p>
                        <button class="btn btn--primary" onclick="openModal('create-folder-modal')" style="margin-top:var(--space-4);">
                            <i class="fa-solid fa-plus"></i> Crear carpeta
                        </button>
                    </div>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap:var(--space-3);">
                        <?php foreach ($folders as $f): ?>
                            <a href="/filemanager/admin/shared/view/<?= (int) $f['id'] ?>" style="text-decoration:none; color:inherit;">
                                <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid <?= $f['is_public'] ? 'var(--color-primary)' : 'var(--color-accent)' ?>; border-radius:var(--radius-md); padding:var(--space-4); box-shadow:var(--shadow-neu); transition:all 0.2s;">
                                    <div style="display:flex; align-items:start; justify-content:space-between; gap:var(--space-2);">
                                        <div style="flex:1; min-width:0;">
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
                                                <?php if (!$f['is_public'] && empty($f['department_name'])): ?>
                                                    <span style="background:var(--color-bg); color:var(--color-text-soft); padding:2px 8px; border-radius:var(--radius-full); font-size:10px; font-weight:700;">
                                                        PRIVADA
                                                    </span>
                                                <?php endif; ?>
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

    <div class="modal-overlay" id="create-folder-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-folder-plus"></i></div>
                <div class="modal__title">Nueva carpeta compartida</div>
            </div>
            <form method="POST" action="/filemanager/admin/shared/create" class="modal__form">
                <div>
                    <label class="modal__label">Nombre</label>
                    <input type="text" name="name" class="modal__input" required autocomplete="off">
                </div>
                <div>
                    <label class="modal__label">Departamento (opcional)</label>
                    <select name="department_id" class="modal__input">
                        <option value="">Sin departamento específico</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:flex; align-items:center; gap:var(--space-2); cursor:pointer; font-size:13px;">
                        <input type="checkbox" name="is_public" style="width:auto;">
                        <span>Pública (todos pueden leer)</span>
                    </label>
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('create-folder-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Crear</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>
</body>
</html>
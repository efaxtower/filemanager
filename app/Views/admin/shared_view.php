<?php
$pageTitle = 'Admin - ' . htmlspecialchars($folder['name']);
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3); min-width:0;">
                    <a href="/filemanager/admin/shared" class="btn-icon" title="Volver">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:15px;"><?= htmlspecialchars($folder['name']) ?></strong>
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
                    <!-- Configuración de la carpeta -->
                    <form method="POST" action="/filemanager/admin/shared/update" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:var(--space-5); margin-bottom:var(--space-4); box-shadow:var(--shadow-neu);">
                        <input type="hidden" name="folder_id" value="<?= (int) $folder['id'] ?>">
                        <h3 style="font-size:14px; font-weight:600; color:var(--color-text-soft); text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-4);">
                            <i class="fa-solid fa-gear"></i> Configuración de la carpeta
                        </h3>

                        <div style="display:flex; flex-direction:column; gap:var(--space-4);">
                            <div>
                                <label class="modal__label">Departamento</label>
                                <select name="department_id" class="modal__input">
                                    <option value="">Sin departamento específico</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= (int) $d['id'] ?>" <?= (int) $folder['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display:flex; align-items:center; gap:var(--space-2); cursor:pointer; font-size:13px;">
                                    <input type="checkbox" name="is_public" style="width:auto;" <?= $folder['is_public'] ? 'checked' : '' ?>>
                                    <span>Pública (todos pueden leer)</span>
                                </label>
                            </div>
                            <div style="display:flex; justify-content:space-between; gap:var(--space-2);">
                                <button type="button" class="btn btn--ghost" style="color:var(--color-error); border-color:var(--color-error);" onclick="if(confirm('¿Eliminar esta carpeta y todo su contenido?')) { document.getElementById('delete-folder-form').submit(); }">
                                    <i class="fa-solid fa-trash"></i> Eliminar carpeta
                                </button>
                                <button type="submit" class="btn btn--primary">
                                    <i class="fa-solid fa-check"></i> Guardar cambios
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-folder-form" method="POST" action="/filemanager/admin/shared/delete" style="display:none;">
                        <input type="hidden" name="folder_id" value="<?= (int) $folder['id'] ?>">
                    </form>

                    <!-- Permisos individuales -->
                    <div style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:var(--space-5); box-shadow:var(--shadow-neu);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-4); gap:var(--space-3); flex-wrap:wrap;">
                            <h3 style="font-size:14px; font-weight:600; color:var(--color-text-soft); text-transform:uppercase; letter-spacing:1px;">
                                <i class="fa-solid fa-user-lock"></i> Permisos individuales
                            </h3>
                            <button class="btn btn--primary" onclick="openModal('add-perm-modal')">
                                <i class="fa-solid fa-plus"></i> Añadir permiso
                            </button>
                        </div>

                        <?php if (empty($permissions)): ?>
                            <p style="text-align:center; color:var(--color-text-soft); padding:var(--space-4);">
                                Sin permisos individuales. Esta carpeta es visible solo para el departamento asignado o público.
                            </p>
                        <?php else: ?>
                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                    <thead>
                                        <tr style="text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--color-text-soft); border-bottom:1px solid var(--color-border);">
                                            <th style="padding:var(--space-2);">Usuario</th>
                                            <th style="padding:var(--space-2);">Leer</th>
                                            <th style="padding:var(--space-2);">Escribir</th>
                                            <th style="padding:var(--space-2);">Borrar</th>
                                            <th style="padding:var(--space-2); text-align:right;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($permissions as $p): ?>
                                            <tr style="border-bottom:1px solid var(--color-border);">
                                                <td style="padding:var(--space-2); font-weight:600;">
                                                    <i class="fa-solid fa-user" style="color:var(--color-accent);"></i>
                                                    <?= htmlspecialchars($p['username']) ?>
                                                    <?php if (!empty($p['department_name'])): ?>
                                                        <small style="color:var(--color-text-soft); font-weight:400;">· <?= htmlspecialchars($p['department_name']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding:var(--space-2);">
                                                    <i class="fa-solid <?= $p['can_read'] ? 'fa-check' : 'fa-xmark' ?>" style="color:<?= $p['can_read'] ? 'var(--color-success)' : 'var(--color-text-soft)' ?>;"></i>
                                                </td>
                                                <td style="padding:var(--space-2);">
                                                    <i class="fa-solid <?= $p['can_write'] ? 'fa-check' : 'fa-xmark' ?>" style="color:<?= $p['can_write'] ? 'var(--color-success)' : 'var(--color-text-soft)' ?>;"></i>
                                                </td>
                                                <td style="padding:var(--space-2);">
                                                    <i class="fa-solid <?= $p['can_delete'] ? 'fa-check' : 'fa-xmark' ?>" style="color:<?= $p['can_delete'] ? 'var(--color-success)' : 'var(--color-text-soft)' ?>;"></i>
                                                </td>
                                                <td style="padding:var(--space-2); text-align:right;">
                                                    <form method="POST" action="/filemanager/admin/shared/permission/delete" style="display:inline;" onsubmit="return confirm('¿Quitar este permiso?');">
                                                        <input type="hidden" name="folder_id" value="<?= (int) $folder['id'] ?>">
                                                        <input type="hidden" name="user_id" value="<?= (int) $p['user_id'] ?>">
                                                        <button type="submit" class="btn-icon" style="width:30px; height:30px; font-size:12px; color:var(--color-error);" title="Eliminar">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="add-perm-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-user-lock"></i></div>
                <div class="modal__title">Permiso individual</div>
            </div>
            <form method="POST" action="/filemanager/admin/shared/permission/set" class="modal__form">
                <input type="hidden" name="folder_id" value="<?= (int) $folder['id'] ?>">
                <div>
                    <label class="modal__label">Usuario</label>
                    <select name="user_id" class="modal__input" required>
                        <option value="">Selecciona un usuario</option>
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= (int) $u['id'] ?>">
                                <?= htmlspecialchars($u['username']) ?>
                                <?php if (!empty($u['department_name'])): ?>
                                    · <?= htmlspecialchars($u['department_name']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:var(--space-2);">
                    <label style="display:flex; align-items:center; gap:var(--space-2); cursor:pointer; font-size:13px;">
                        <input type="checkbox" name="can_read" value="1" checked style="width:auto;">
                        <span>Puede leer</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:var(--space-2); cursor:pointer; font-size:13px;">
                        <input type="checkbox" name="can_write" value="1" style="width:auto;">
                        <span>Puede escribir (subir archivos)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:var(--space-2); cursor:pointer; font-size:13px;">
                        <input type="checkbox" name="can_delete" value="1" style="width:auto;">
                        <span>Puede borrar</span>
                    </label>
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('add-perm-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Guardar</button>
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
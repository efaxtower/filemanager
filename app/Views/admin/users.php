<?php
$pageTitle = 'Admin - Usuarios';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-users" style="color:var(--color-accent);"></i> Gestión de usuarios</strong>
                </div>
                <div class="topbar__actions">
                    <button class="btn btn--primary" onclick="openModal('create-user-modal')">
                        <i class="fa-solid fa-user-plus"></i> Crear usuario
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

                <div style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--color-bg); text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--color-text-soft);">
                                <th style="padding:var(--space-3);">Usuario</th>
                                <th style="padding:var(--space-3);">Rol</th>
                                <th style="padding:var(--space-3);">Departamento</th>
                                <th style="padding:var(--space-3);">Creado</th>
                                <th style="padding:var(--space-3); text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr style="border-top:1px solid var(--color-border);">
                                    <td style="padding:var(--space-3); font-weight:600;">
                                        <i class="fa-solid fa-user" style="color:var(--color-accent); margin-right:var(--space-2);"></i>
                                        <?= htmlspecialchars($u['username']) ?>
                                    </td>
                                    <td style="padding:var(--space-3);">
                                        <form method="POST" action="/filemanager/admin/users/update" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <input type="hidden" name="department_id" value="<?= (int) ($u['department_id'] ?? 0) ?>">
                                            <select name="role" class="input" style="padding:4px 8px; font-size:12px;" onchange="this.form.submit()">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="padding:var(--space-3);">
                                        <form method="POST" action="/filemanager/admin/users/update" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <input type="hidden" name="role" value="<?= htmlspecialchars($u['role']) ?>">
                                            <select name="department_id" class="input" style="padding:4px 8px; font-size:12px;" onchange="this.form.submit()">
                                                <option value="">Sin departamento</option>
                                                <?php foreach ($departments as $d): ?>
                                                    <option value="<?= (int) $d['id'] ?>" <?= (int) $u['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($d['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="padding:var(--space-3); color:var(--color-text-soft); font-size:12px;">
                                        <?= date('d M Y', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td style="padding:var(--space-3); text-align:right;">
                                        <div style="display:flex; gap:var(--space-1); justify-content:flex-end;">
                                            <button type="button" class="btn-icon" style="width:32px; height:32px; font-size:13px;" title="Resetear contraseña" onclick="openResetModal(<?= (int) $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-key"></i>
                                            </button>
                                            <form method="POST" action="/filemanager/admin/users/delete" style="display:inline;" onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($u['username'], ENT_QUOTES) ?>? Se borrarán todos sus archivos.');">
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <button type="submit" class="btn-icon" style="width:32px; height:32px; font-size:13px; color:var(--color-error);" title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="create-user-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-user-plus"></i></div>
                <div class="modal__title">Crear usuario</div>
            </div>
            <form method="POST" action="/filemanager/admin/users/create" class="modal__form">
                <div>
                    <label class="modal__label">Usuario</label>
                    <input type="text" name="username" class="modal__input" required pattern="[a-zA-Z0-9_]{3,50}" autocomplete="off">
                </div>
                <div>
                    <label class="modal__label">Contraseña (mín. 8)</label>
                    <input type="text" name="password" class="modal__input" required minlength="8" autocomplete="off">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-3);">
                    <div>
                        <label class="modal__label">Rol</label>
                        <select name="role" class="modal__input">
                            <option value="user">Usuario</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="modal__label">Departamento</label>
                        <select name="department_id" class="modal__input">
                            <option value="">Sin departamento</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="modal__label">Cuota (GB)</label>
                    <input type="number" name="quota_gb" class="modal__input" value="15" min="1" max="1000">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('create-user-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="reset-password-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-key"></i></div>
                <div class="modal__title">Resetear contraseña de <span id="reset-username"></span></div>
            </div>
            <form method="POST" action="/filemanager/admin/users/reset-password" class="modal__form">
                <input type="hidden" name="user_id" id="reset-user-id">
                <div>
                    <label class="modal__label">Nueva contraseña (mín. 8)</label>
                    <input type="text" name="new_password" class="modal__input" required minlength="8" autocomplete="off">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('reset-password-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Resetear</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        function openResetModal(userId, username) {
            document.getElementById('reset-user-id').value = userId;
            document.getElementById('reset-username').textContent = username;
            openModal('reset-password-modal');
        }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>
</body>
</html>
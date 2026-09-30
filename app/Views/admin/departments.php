<?php
$pageTitle = 'Admin - Departamentos';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-building" style="color:var(--color-accent);"></i> Gestión de departamentos</strong>
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

                <div class="actions-bar">
                    <form method="POST" action="/filemanager/admin/departments/create" class="form-inline" style="flex:1;">
                        <input type="text" name="name" class="input" placeholder="Nombre" required style="flex:0 0 200px;">
                        <input type="text" name="description" class="input" placeholder="Descripción (opcional)" style="flex:1;">
                        <button type="submit" class="btn btn--primary">
                            <i class="fa-solid fa-plus"></i> Crear
                        </button>
                    </form>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(min(280px, 100%), 1fr)); gap:var(--space-3);">
                    <?php foreach ($departments as $d): ?>
                        <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid var(--color-accent); border-radius:var(--radius-md); padding:var(--space-4); box-shadow:var(--shadow-neu);">
                            <div style="display:flex; justify-content:space-between; align-items:start; gap:var(--space-2);">
                                <div style="flex:1; min-width:0;">
                                    <h3 style="font-size:15px; font-weight:600; color:var(--color-text); word-break:break-word;">
                                        <i class="fa-solid fa-building" style="color:var(--color-accent);"></i>
                                        <?= htmlspecialchars($d['name']) ?>
                                    </h3>
                                    <p style="color:var(--color-text-soft); font-size:12px; margin-top:4px; word-break:break-word;">
                                        <?= htmlspecialchars($d['description'] ?? 'Sin descripción') ?>
                                    </p>
                                    <p style="color:var(--color-text-soft); font-size:11px; margin-top:var(--space-2);">
                                        <i class="fa-solid fa-user"></i>
                                        <?= (int) $d['user_count'] ?> usuario<?= $d['user_count'] === 1 ? '' : 's' ?>
                                    </p>
                                </div>
                                <div style="display:flex; gap:var(--space-1); flex-shrink:0;">
                                    <button type="button" class="btn-icon" style="width:32px; height:32px; font-size:13px;" title="Editar" onclick="openEditModal(<?= (int) $d['id'] ?>)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="/filemanager/admin/departments/delete" onsubmit="return confirm('¿Borrar este departamento?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                                        <button type="submit" class="btn-icon" style="width:32px; height:32px; font-size:13px; color:var(--color-error);" title="Borrar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="edit-dept-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon"><i class="fa-solid fa-pen"></i></div>
                <div class="modal__title">Editar departamento</div>
            </div>
            <form method="POST" action="/filemanager/admin/departments/update" class="modal__form">
                <input type="hidden" name="id" id="edit-dept-id">
                <div>
                    <label class="modal__label">Nombre</label>
                    <input type="text" name="name" id="edit-dept-name" class="modal__input" required>
                </div>
                <div>
                    <label class="modal__label">Descripción</label>
                    <input type="text" name="description" id="edit-dept-description" class="modal__input">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('edit-dept-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        async function openEditModal(id) {
            try {
                const res = await fetch('/filemanager/admin/departments/edit?id=' + id);
                const data = await res.json();
                if (data.error) { alert(data.error); return; }
                document.getElementById('edit-dept-id').value = data.id;
                document.getElementById('edit-dept-name').value = data.name;
                document.getElementById('edit-dept-description').value = data.description;
                openModal('edit-dept-modal');
            } catch (e) {
                alert('Error al cargar el departamento');
            }
        }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>
</body>
</html>
<?php
$pageTitle = 'Nuevo reporte';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1;">
                    <strong style="font-size:16px;"><i class="fa-solid fa-plus" style="color:var(--color-accent);"></i> Nuevo reporte</strong>
                </div>
                <div class="topbar__actions">
                    <a href="/filemanager/reports" class="btn btn--ghost">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content">
                <div style="max-width:700px;">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert--error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="/filemanager/reports/create" style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:var(--space-5); box-shadow:var(--shadow-neu);">
                        <div style="display:flex; flex-direction:column; gap:var(--space-4);">
                            <div>
                                <label class="modal__label">Asunto *</label>
                                <input type="text" name="subject" class="modal__input" required maxlength="150" autocomplete="off" placeholder="Ej: No puedo subir archivos">
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-3);">
                                <div>
                                    <label class="modal__label">Categoría</label>
                                    <select name="category" class="modal__input">
                                        <option value="bug">Bug / Error</option>
                                        <option value="suggestion">Sugerencia</option>
                                        <option value="complaint">Queja</option>
                                        <option value="other" selected>Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="modal__label">Prioridad</label>
                                    <select name="priority" class="modal__input">
                                        <option value="low">Baja</option>
                                        <option value="medium" selected>Media</option>
                                        <option value="high">Alta</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="modal__label">Descripción * (mín. 10 caracteres)</label>
                                <textarea name="description" class="modal__input" rows="6" required minlength="10" placeholder="Describe el problema o sugerencia con detalle..."></textarea>
                            </div>

                            <div style="display:flex; justify-content:flex-end; gap:var(--space-2);">
                                <a href="/filemanager/reports" class="btn btn--ghost">Cancelar</a>
                                <button type="submit" class="btn btn--primary">
                                    <i class="fa-solid fa-paper-plane"></i> Enviar reporte
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
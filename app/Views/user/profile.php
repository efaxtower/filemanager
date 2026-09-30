<?php
$pageTitle = 'Mi perfil';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1;">
                    <strong style="font-size:16px;"><i class="fa-solid fa-user-gear" style="color:var(--color-accent);"></i> Mi perfil</strong>
                </div>
                <div class="topbar__actions">
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content">
                <div style="max-width:600px;">
                    <div style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:var(--space-5); margin-bottom:var(--space-4); box-shadow:var(--shadow-neu);">
                        <h3 style="font-size:14px; font-weight:600; color:var(--color-text-soft); text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-3);">Información</h3>
                        <p style="font-size:13px; margin-bottom:var(--space-2);">
                            <strong>Usuario:</strong> <?= htmlspecialchars($user['username']) ?>
                        </p>
                        <p style="font-size:13px; margin-bottom:var(--space-2);">
                            <strong>Rol:</strong> <?= $user['role'] === 'admin' ? 'Admin' : 'Usuario' ?>
                        </p>
                        <p style="font-size:13px;">
                            <strong>Cuota:</strong> <?= number_format($user['quota_bytes'] / 1024 / 1024 / 1024, 2) ?> GB
                        </p>
                    </div>

                    <div style="background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); padding:var(--space-5); box-shadow:var(--shadow-neu);">
                        <h3 style="font-size:14px; font-weight:600; color:var(--color-text-soft); text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-4);">
                            <i class="fa-solid fa-key"></i> Cambiar contraseña
                        </h3>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert--error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert--success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <form method="POST" action="/filemanager/profile/password" class="modal__form">
                            <div>
                                <label class="modal__label">Contraseña actual</label>
                                <input type="password" name="current_password" class="modal__input" required autocomplete="current-password">
                            </div>
                            <div>
                                <label class="modal__label">Nueva contraseña (mín. 8)</label>
                                <input type="password" name="new_password" class="modal__input" required minlength="8" autocomplete="new-password">
                            </div>
                            <div>
                                <label class="modal__label">Confirmar nueva contraseña</label>
                                <input type="password" name="confirm_password" class="modal__input" required minlength="8" autocomplete="new-password">
                            </div>
                            <div style="display:flex; justify-content:flex-end;">
                                <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Cambiar contraseña</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
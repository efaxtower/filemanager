<?php
$pageTitle = 'Login';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__logo">
                <div class="hexagon"><i class="fa-solid fa-folder-open"></i></div>
                <span>FileManager</span>
            </div>

            <h1>Iniciar sesión</h1>

            <?php if ($error): ?>
                <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert--success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="/filemanager/login">
                <label>
                    Usuario
                    <input type="text" name="username" required autofocus>
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" required>
                </label>
                <button type="submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Entrar
                </button>
            </form>

            <div class="auth-card__footer">
                ¿No tienes cuenta? <a href="/filemanager/register">Solicita una aquí</a>
            </div>

            <div style="text-align:center; margin-top:var(--space-3);">
                <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
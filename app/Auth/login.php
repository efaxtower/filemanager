<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FileManager</title>
    <link rel="stylesheet" href="/filemanager/public/assets/css/app.css">
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__logo">
                <div class="hexagon">FM</div>
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
                    <input type="text" name="username" required>
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" required>
                </label>
                <button type="submit">Entrar</button>
            </form>

            <div class="auth-card__footer">
                ¿No tienes cuenta? <a href="/filemanager/register">Regístrate</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$pageTitle = 'Solicitar cuenta';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__logo">
                <div class="hexagon"><i class="fa-solid fa-folder-open"></i></div>
                <span>FileManager</span>
            </div>

            <h1>Solicitar cuenta</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="/filemanager/register">
                <label>
                    Usuario deseado
                    <input type="text" name="username" required
                           pattern="[a-zA-Z0-9_]{3,50}"
                           title="Solo letras sin tildes, números y guion bajo (3-50 caracteres)"
                           autocomplete="off">
                </label>
                <label>
                    Departamento
                    <select name="department_id" style="padding:var(--space-2) var(--space-3); background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); color:var(--color-text);">
                        <option value="">Sin preferencia</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Mensaje (opcional)
                    <textarea name="message" rows="2" style="padding:var(--space-2) var(--space-3); background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); color:var(--color-text); resize:vertical; font-family:inherit; font-size:14px;" placeholder="Cuéntanos por qué quieres una cuenta..."></textarea>
                </label>

                <label>
                    Captcha
                    <div style="display:flex; gap:var(--space-2); align-items:center;">
                        <img src="/filemanager/captcha" alt="Captcha"
                             style="border-radius:var(--radius-sm); border:1px solid var(--color-border); cursor:pointer; flex-shrink:0; height:60px; width:auto;"
                             onclick="this.src='/filemanager/captcha?'+Date.now()"
                             title="Clic para recargar">
                        <input type="text" name="captcha" required maxlength="5" autocomplete="off"
                               style="flex:1; min-width:0; text-transform:uppercase; letter-spacing:4px; font-weight:700; text-align:center; font-size:16px;"
                               placeholder="— — — — —">
                    </div>
                </label>

                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i> Enviar solicitud
                </button>
            </form>

            <div class="auth-card__footer">
                ¿Ya tienes cuenta? <a href="/filemanager/login">Inicia sesión</a>
            </div>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
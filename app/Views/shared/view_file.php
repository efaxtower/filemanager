<?php
$pageTitle = htmlspecialchars($file['name']);
require __DIR__ . '/../partials/head.php';

// Detección de archivo de texto editable (misma lógica que en SharedController)
$editableMime = $mime;
$editableName = $file['name'];
$isEditable = false;

if (str_starts_with($editableMime, 'text/')) $isEditable = true;
if (in_array($editableMime, ['application/json', 'application/xml', 'application/javascript', 'application/x-httpd-php'], true)) $isEditable = true;

$ext = strtolower(pathinfo($editableName, PATHINFO_EXTENSION));
$editableExtensions = ['txt', 'md', 'json', 'csv', 'xml', 'html', 'htm', 'css', 'js', 'ts', 'sql', 'log', 'yml', 'yaml', 'ini', 'conf', 'env', 'php', 'py', 'sh', 'bat'];
if (in_array($ext, $editableExtensions, true)) $isEditable = true;

// Límite para edición
if ($size > 1024 * 1024) $isEditable = false;

// Permisos de escritura
$canWriteFile = $this->shared->canUserAccess(
    (int) $file['folder_id'],
    (int) $user['id'],
    $user['department_id'] ? (int) $user['department_id'] : null,
    $user['role'] === 'admin',
    'write'
);
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3); min-width:0;">
                    <a href="/filemanager/shared/view/<?= (int) $folder['id'] ?>" class="btn-icon" title="Volver">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <strong style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:15px;"><?= htmlspecialchars($file['name']) ?></strong>
                    <span class="quota"><i class="fa-solid fa-weight-hanging"></i> <?= number_format($size / 1024, 2) ?> KB</span>
                </div>
                <div class="topbar__actions">
                    <?php if ($canWriteFile): ?>
                        <button class="btn btn--ghost" onclick="document.getElementById('replace-input').click();">
                            <i class="fa-solid fa-rotate"></i> Reemplazar
                        </button>
                        <form method="POST" action="/filemanager/shared/replace-file" enctype="multipart/form-data" style="display:none;">
                            <input type="hidden" name="file_id" value="<?= (int) $file['id'] ?>">
                            <input type="file" name="file" id="replace-input" onchange="this.form.submit()">
                        </form>
                    <?php endif; ?>
                    <a href="/filemanager/shared/download/<?= (int) $file['id'] ?>" class="btn btn--accent">
                        <i class="fa-solid fa-download"></i> Descargar
                    </a>
                    <button class="btn-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
            </header>

            <div class="content" style="padding:0; display:flex; flex-direction:column;">

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert--error" style="margin:var(--space-4);"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert--success" style="margin:var(--space-4);"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if ($isEditable && $canWriteFile): ?>
                    <!-- EDITOR DE TEXTO -->
                    <form method="POST" action="/filemanager/shared/save-file" style="display:flex; flex-direction:column; flex:1; min-height:0;">
                        <input type="hidden" name="file_id" value="<?= (int) $file['id'] ?>">
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:var(--space-3) var(--space-4); background:var(--glass-bg); backdrop-filter:blur(var(--glass-blur)); border-bottom:1px solid var(--glass-border);">
                            <span style="font-size:12px; color:var(--color-text-soft); font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                                <i class="fa-solid fa-pen"></i> Editor de texto
                            </span>
                            <button type="submit" class="btn btn--primary">
                                <i class="fa-solid fa-check"></i> Guardar cambios
                            </button>
                        </div>
                        <textarea name="content"
                                  style="flex:1; width:100%; padding:var(--space-4); margin:0; border:none; outline:none; font-family:'Consolas','Monaco',monospace; font-size:14px; line-height:1.7; background:var(--color-surface); color:var(--color-text); resize:none; min-height:0;"><?= htmlspecialchars($content) ?></textarea>
                    </form>
                <?php elseif ($isEditable): ?>
                    <!-- Solo lectura -->
                    <pre style="flex:1; padding:var(--space-5); margin:0; overflow:auto; font-family:'Consolas','Monaco',monospace; font-size:14px; line-height:1.7; background:var(--color-surface); color:var(--color-text);"><?= htmlspecialchars($content) ?></pre>
                <?php elseif (str_starts_with($mime, 'image/')): ?>
                    <div style="display:flex; align-items:center; justify-content:center; flex:1; padding:var(--space-4);">
                        <img src="data:<?= htmlspecialchars($mime) ?>;base64,<?= base64_encode($content) ?>" alt="<?= htmlspecialchars($file['name']) ?>" style="max-width:100%; max-height:100%; border-radius:var(--radius-md); box-shadow:var(--shadow-lg);">
                    </div>
                <?php elseif ($mime === 'application/pdf'): ?>
                    <iframe src="data:application/pdf;base64,<?= base64_encode($content) ?>" style="flex:1; width:100%; border:none;"></iframe>
                <?php else: ?>
                    <div style="display:flex; align-items:center; justify-content:center; flex:1; padding:var(--space-6);">
                        <div style="background:var(--glass-bg); backdrop-filter:blur(var(--glass-blur)); border:1px solid var(--glass-border); border-radius:var(--radius-lg); padding:var(--space-6); max-width:500px; text-align:center; box-shadow:var(--shadow-lg);">
                            <div style="width:100px; height:100px; margin:0 auto var(--space-4); background:var(--gradient-mixed); clip-path:polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%); display:flex; align-items:center; justify-content:center; font-size:44px; color:white; animation:float 4s ease-in-out infinite;">
                                <i class="fa-solid fa-file"></i>
                            </div>
                            <h2 style="color:var(--color-text); font-size:18px; margin-bottom:var(--space-2); word-break:break-word;"><?= htmlspecialchars($file['name']) ?></h2>
                            <p style="color:var(--color-text-soft); font-size:12px; margin-bottom:var(--space-4);">Este tipo de archivo no se puede previsualizar.</p>
                            <a href="/filemanager/shared/download/<?= (int) $file['id'] ?>" class="btn btn--accent">
                                <i class="fa-solid fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
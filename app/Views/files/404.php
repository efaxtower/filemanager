<?php
$pageTitle = '404';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <div class="content" style="display:flex; align-items:center; justify-content:center;">
                <div class="empty">
                    <div class="empty__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <h2>404 - No encontrado</h2>
                    <p>La ruta que buscas no existe.</p>
                    <a href="/filemanager/" class="btn btn--primary" style="margin-top:var(--space-4);">
                        <i class="fa-solid fa-arrow-left"></i> Volver a la raíz
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
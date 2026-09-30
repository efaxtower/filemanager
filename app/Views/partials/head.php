<?php
$pageTitle = $pageTitle ?? 'FileManager';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - FileManager</title>
    <link rel="stylesheet" href="/filemanager/public/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/filemanager/public/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
</head>
<?php
use App\Auth\Auth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sidebarUser = (new Auth())->currentUser();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath = '/filemanager';
if (str_starts_with($currentUri, $basePath)) {
    $currentUri = substr($currentUri, strlen($basePath));
}
$currentUri = $currentUri ?: '/';

$isActive = function(string $path) use ($currentUri): string {
    if ($path === '/') {
        return ($currentUri === '/' || $currentUri === '') ? ' active' : '';
    }
    return str_starts_with($currentUri, $path) ? ' active' : '';
};
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar__logo">
        <div class="hexagon"><i class="fa-solid fa-folder-open"></i></div>
        <span>FileManager</span>
    </div>

    <?php if ($sidebarUser): ?>
        <a href="/filemanager/" class="sidebar__new">
            <i class="fa-solid fa-plus"></i> Nuevo
        </a>
    <?php endif; ?>

    <?php if ($sidebarUser): ?>
    <div class="sidebar__section open">
        <div class="sidebar__section-header" onclick="toggleSection(this)">
            <i class="fa-solid fa-chevron-right arrow"></i>
            <span>Mi unidad</span>
        </div>
        <div class="sidebar__section-content">
            <a href="/filemanager/" class="sidebar__link<?= $isActive('/') ?>">
                <i class="fa-solid fa-hard-drive"></i>
                Inicio
            </a>

                        <a href="/filemanager/shared" class="sidebar__link<?= $isActive('/shared') ?>">
                <i class="fa-solid fa-folder-tree"></i>
                Compartidos
            </a>
            <a href="/filemanager/reports" class="sidebar__link<?= $isActive('/reports') ?>">
                <i class="fa-solid fa-flag"></i>
                Mis reportes
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($sidebarUser && $sidebarUser['role'] === 'admin'): ?>
    <div class="sidebar__section open">
        <div class="sidebar__section-header" onclick="toggleSection(this)">
            <i class="fa-solid fa-chevron-right arrow"></i>
            <span>Admin</span>
        </div>
        <div class="sidebar__section-content">
            <a href="/filemanager/admin/users" class="sidebar__link<?= $isActive('/admin/users') ?>">
                <i class="fa-solid fa-users"></i>
                Usuarios
            </a>
                        <a href="/filemanager/admin/shared" class="sidebar__link<?= $isActive('/admin/shared') ?>">
                <i class="fa-solid fa-folder-tree"></i>
                Carpetas compartidas
            </a>
            <a href="/filemanager/admin/departments" class="sidebar__link<?= $isActive('/admin/departments') ?>">
                <i class="fa-solid fa-building"></i>
                Departamentos
            </a>
            <a href="/filemanager/admin/requests" class="sidebar__link<?= $isActive('/admin/requests') ?>">
                <i class="fa-solid fa-envelope-open-text"></i>
                Solicitudes
            </a>
            <a href="/filemanager/admin/reports" class="sidebar__link<?= $isActive('/admin/reports') ?>">
                <i class="fa-solid fa-clipboard-list"></i>
                Reportes
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($sidebarUser): ?>
    <div class="sidebar__section open">
        <div class="sidebar__section-header" onclick="toggleSection(this)">
            <i class="fa-solid fa-chevron-right arrow"></i>
            <span>Cuenta</span>
        </div>
        <div class="sidebar__section-content">
            <a href="/filemanager/profile" class="sidebar__link<?= $isActive('/profile') ?>">
                <i class="fa-solid fa-user-gear"></i>
                Mi perfil
            </a>
            <a href="/filemanager/logout" class="sidebar__link">
                <i class="fa-solid fa-right-from-bracket"></i>
                Cerrar sesión
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="sidebar__section open">
        <div class="sidebar__section-header" onclick="toggleSection(this)">
            <i class="fa-solid fa-chevron-right arrow"></i>
            <span>Acceso</span>
        </div>
        <div class="sidebar__section-content">
            <a href="/filemanager/login" class="sidebar__link<?= $isActive('/login') ?>">
                <i class="fa-solid fa-right-to-bracket"></i>
                Iniciar sesión
            </a>
            <a href="/filemanager/register" class="sidebar__link<?= $isActive('/register') ?>">
                <i class="fa-solid fa-user-plus"></i>
                Solicitar cuenta
            </a>
        </div>
    </div>
    <?php endif; ?>
</aside>
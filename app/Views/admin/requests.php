<?php
$pageTitle = 'Admin - Solicitudes';
require __DIR__ . '/../partials/head.php';
?>
<body>
    <div class="app">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main">
            <header class="topbar">
                <div style="flex:1; display:flex; align-items:center; gap:var(--space-3);">
                    <strong style="font-size:16px;"><i class="fa-solid fa-envelope-open-text" style="color:var(--color-accent);"></i> Solicitudes de cuenta</strong>
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
                    <a href="/filemanager/admin/requests?status=pending" class="btn <?= $status === 'pending' ? 'btn--primary' : 'btn--ghost' ?>">
                        <i class="fa-solid fa-clock"></i> Pendientes
                    </a>
                    <a href="/filemanager/admin/requests?status=approved" class="btn <?= $status === 'approved' ? 'btn--primary' : 'btn--ghost' ?>">
                        <i class="fa-solid fa-check"></i> Aprobadas
                    </a>
                    <a href="/filemanager/admin/requests?status=rejected" class="btn <?= $status === 'rejected' ? 'btn--primary' : 'btn--ghost' ?>">
                        <i class="fa-solid fa-xmark"></i> Rechazadas
                    </a>
                    <a href="/filemanager/admin/requests?status=all" class="btn <?= $status === 'all' ? 'btn--primary' : 'btn--ghost' ?>">
                        <i class="fa-solid fa-list"></i> Todas
                    </a>
                </div>

                <?php if (empty($requests)): ?>
                    <div class="empty">
                        <div class="empty__icon"><i class="fa-regular fa-envelope"></i></div>
                        <h2>Sin solicitudes</h2>
                        <p>No hay solicitudes en este estado</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:var(--space-3);">
                        <?php foreach ($requests as $r): ?>
                            <?php
                            $statusColors = [
                                'pending' => ['bg' => 'var(--color-accent-light)', 'color' => 'var(--color-accent-dark)', 'icon' => 'fa-clock', 'label' => 'PENDIENTE'],
                                'approved' => ['bg' => 'var(--color-success-bg)', 'color' => 'var(--color-success)', 'icon' => 'fa-check', 'label' => 'APROBADA'],
                                'rejected' => ['bg' => 'var(--color-error-bg)', 'color' => 'var(--color-error)', 'icon' => 'fa-xmark', 'label' => 'RECHAZADA'],
                                'cancelled' => ['bg' => 'var(--color-bg)', 'color' => 'var(--color-text-soft)', 'icon' => 'fa-ban', 'label' => 'CANCELADA'],
                            ];
                            $sc = $statusColors[$r['status']] ?? $statusColors['pending'];
                            ?>
                            <div style="background:var(--color-surface); border:1px solid var(--color-border); border-left:3px solid <?= $sc['color'] ?>; border-radius:var(--radius-md); padding:var(--space-4); box-shadow:var(--shadow-neu);">
                                <div style="display:flex; justify-content:space-between; align-items:start; gap:var(--space-3); flex-wrap:wrap;">
                                    <div style="flex:1; min-width:200px;">
                                        <div style="display:flex; align-items:center; gap:var(--space-3); margin-bottom:var(--space-2); flex-wrap:wrap;">
                                            <h3 style="font-size:16px; font-weight:600; color:var(--color-text);">
                                                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($r['username']) ?>
                                            </h3>
                                            <span style="background:<?= $sc['bg'] ?>; color:<?= $sc['color'] ?>; padding:3px 10px; border-radius:var(--radius-full); font-size:10px; font-weight:700;">
                                                <i class="fa-solid <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                                            </span>
                                        </div>
                                        <p style="font-size:12px; color:var(--color-text-soft); margin-bottom:var(--space-1);">
                                            <i class="fa-solid fa-building"></i> <?= htmlspecialchars($r['department_name'] ?? 'Sin preferencia') ?>
                                        </p>
                                        <p style="font-size:12px; color:var(--color-text-soft); margin-bottom:var(--space-1);">
                                            <i class="fa-solid fa-calendar"></i> <?= date('d M Y H:i', strtotime($r['created_at'])) ?>
                                        </p>
                                        <?php if (!empty($r['message'])): ?>
                                            <div style="background:var(--color-bg); padding:var(--space-3); border-radius:var(--radius-sm); margin-top:var(--space-3); font-size:13px; color:var(--color-text);">
                                                <i class="fa-solid fa-quote-left" style="color:var(--color-text-soft);"></i>
                                                <?= nl2br(htmlspecialchars($r['message'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($r['status'] === 'rejected' && !empty($r['rejection_reason'])): ?>
                                            <p style="font-size:12px; color:var(--color-error); margin-top:var(--space-2);">
                                                <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($r['rejection_reason']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($r['status'] === 'pending'): ?>
                                        <div style="display:flex; gap:var(--space-2); flex-shrink:0;">
                                            <button type="button" class="btn btn--primary" onclick="openApproveModal(<?= (int) $r['id'] ?>, '<?= htmlspecialchars($r['username'], ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-check"></i> Aprobar
                                            </button>
                                            <button type="button" class="btn btn--ghost" style="color:var(--color-error); border-color:var(--color-error);" onclick="openRejectModal(<?= (int) $r['id'] ?>, '<?= htmlspecialchars($r['username'], ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-xmark"></i> Rechazar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="approve-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon" style="background:var(--gradient-primary);"><i class="fa-solid fa-check"></i></div>
                <div class="modal__title">Aprobar solicitud de <span id="approve-username"></span></div>
            </div>
            <form method="POST" action="/filemanager/admin/requests/approve" class="modal__form">
                <input type="hidden" name="request_id" id="approve-request-id">
                <div>
                    <label class="modal__label">Contraseña (mín. 8)</label>
                    <input type="text" name="password" class="modal__input" required minlength="8" autocomplete="off" placeholder="Escribe una contraseña temporal">
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('approve-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="fa-solid fa-check"></i> Crear usuario</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="reject-modal">
        <div class="modal">
            <div class="modal__header">
                <div class="modal__icon" style="background:var(--color-error);"><i class="fa-solid fa-xmark"></i></div>
                <div class="modal__title">Rechazar solicitud de <span id="reject-username"></span></div>
            </div>
            <form method="POST" action="/filemanager/admin/requests/reject" class="modal__form">
                <input type="hidden" name="request_id" id="reject-request-id">
                <div>
                    <label class="modal__label">Motivo</label>
                    <textarea name="reason" class="modal__input" rows="3" required></textarea>
                </div>
                <div class="modal__actions">
                    <button type="button" class="btn btn--ghost" onclick="closeModal('reject-modal')">Cancelar</button>
                    <button type="submit" class="btn btn--primary" style="background:var(--color-error); box-shadow:none;"><i class="fa-solid fa-xmark"></i> Rechazar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/filemanager/public/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        function openApproveModal(id, username) {
            document.getElementById('approve-request-id').value = id;
            document.getElementById('approve-username').textContent = username;
            openModal('approve-modal');
        }
        function openRejectModal(id, username) {
            document.getElementById('reject-request-id').value = id;
            document.getElementById('reject-username').textContent = username;
            openModal('reject-modal');
        }
        document.querySelectorAll('.modal-overlay').forEach(el => {
            el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
        });
    </script>
</body>
</html>
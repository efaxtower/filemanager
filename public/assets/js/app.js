// ============================================================
// TEMA
// ============================================================
(function() {
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
})();

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}

// ============================================================
// SECCIONES DEL SIDEBAR
// ============================================================
function toggleSection(element) {
    element.parentElement.classList.toggle('open');
}

// ============================================================
// VISTA (grid / lista)
// ============================================================
function toggleView(mode) {
    const grid = document.querySelector('.file-grid');
    const list = document.querySelector('.file-list');
    if (!grid || !list) return;

    if (mode === 'list') {
        grid.style.display = 'none';
        list.style.display = 'flex';
        localStorage.setItem('view', 'list');
    } else {
        grid.style.display = 'grid';
        list.style.display = 'none';
        localStorage.setItem('view', 'grid');
    }
}

// ============================================================
// MODAL RENOMBRAR
// ============================================================
function openRenameModal(path, oldName) {
    const modal = document.getElementById('rename-modal');
    if (!modal) return;
    document.getElementById('rename-input').value = oldName;
    document.getElementById('rename-path').value = path;
    document.getElementById('rename-old').value = oldName;
    modal.classList.add('open');
    const input = document.getElementById('rename-input');
    input.focus();
    input.select();
}

function closeRenameModal() {
    const modal = document.getElementById('rename-modal');
    if (modal) modal.classList.remove('open');
}

// ============================================================
// CORTINA DE CARGA (tipo puerta de enrrollable)
// ============================================================
const LOADING_DURATION = 1500; // ms que se queda visible la cortina
const CURTAIN_ANIMATION = 500; // ms que tarda la cortina en bajar

(function() {
    const bar = document.createElement('div');
    bar.className = 'loading-bar';
    bar.id = 'loading-bar';
    document.body.appendChild(bar);

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loading-overlay';

    const hexagons = `
        <div class="loading-hexagons">
            <div class="hex-bg"></div>
            <div class="hex-bg"></div>
            <div class="hex-bg"></div>
        </div>
    `;

    const drops = `
        <div class="loading-drops">
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
            <div class="drop"></div>
        </div>
    `;

    const waves = `
        <div class="loading-drops">
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
            <div class="wave"></div>
        </div>
    `;

    const content = `
        <div class="loading-hexagon">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div class="loading-spinner"></div>
        <div class="loading-text">Cargando</div>
    `;

    overlay.innerHTML = hexagons + drops + waves + content;

    if (sessionStorage.getItem('navigating') === '1') {
        overlay.classList.add('active');
    }

    document.body.appendChild(overlay);
})();

function showLoading() {
    const bar = document.getElementById('loading-bar');
    const overlay = document.getElementById('loading-overlay');
    if (bar) {
        bar.classList.remove('done');
        bar.classList.add('active');
    }
    if (overlay) {
        overlay.classList.remove('reveal');
        void overlay.offsetHeight;
        overlay.classList.add('active');
    }
}

function hideLoading() {
    const bar = document.getElementById('loading-bar');
    const overlay = document.getElementById('loading-overlay');
    if (bar) {
        bar.classList.add('done');
        setTimeout(() => bar.classList.remove('active', 'done'), 400);
    }
    if (overlay) {
        setTimeout(() => {
            overlay.classList.remove('active');
            void overlay.offsetHeight;
            overlay.classList.add('reveal');
        }, LOADING_DURATION);
    }
}

// Marcar que estamos navegando y mostrar la cortina
function startNavigation() {
    sessionStorage.setItem('navigating', '1');
    showLoading();
}

// Clic en enlaces internos: esperar a que la cortina baje antes de navegar
document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href) return;
    if (href.startsWith('#') || href.startsWith('javascript:')) return;
    if (link.target === '_blank') return;
    if (link.hasAttribute('download')) return;
    if (!href.startsWith('/filemanager/')) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;

    e.preventDefault();
    startNavigation();

    // Esperar a que la cortina baje, luego navegar
    setTimeout(() => {
        window.location.href = href;
    }, CURTAIN_ANIMATION);
});

// Enviar formularios: también con delay
document.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    startNavigation();

    setTimeout(() => {
        form.submit();
    }, CURTAIN_ANIMATION);
});

// Al cargar la página
window.addEventListener('pageshow', function() {
    const navigating = sessionStorage.getItem('navigating');
    const overlay = document.getElementById('loading-overlay');
    const bar = document.getElementById('loading-bar');

    if (navigating !== '1') {
        if (overlay) {
            overlay.classList.remove('active');
            overlay.classList.add('reveal');
        }
        if (bar) {
            bar.classList.remove('active', 'done');
        }
        return;
    }

    sessionStorage.removeItem('navigating');

    if (overlay) {
        setTimeout(() => {
            overlay.classList.remove('active');
            void overlay.offsetHeight;
            overlay.classList.add('reveal');
        }, LOADING_DURATION);
    }
    if (bar) {
        bar.classList.add('done');
        setTimeout(() => bar.classList.remove('active', 'done'), 400);
    }
});
// ============================================================
// ANIMACIÓN ESCALONADA + MODALES
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.file-card, .file-row');
    cards.forEach((card, i) => {
        card.style.animationDelay = (i * 0.02) + 's';
    });

    const overlay = document.getElementById('rename-modal');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeRenameModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRenameModal();
    });
});
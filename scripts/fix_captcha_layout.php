<?php

/**
 * Script de corrección automática:
 * 1. Ajusta .auth-page y .auth-card en app.css
 * 2. Ajusta el bloque del captcha en register.php
 * 3. Reduce el espaciado del formulario para que quepa en pantalla
 */

$base = dirname(__DIR__);

// ============================================================
// 1. Arreglar app.css
// ============================================================
$cssFile = $base . '/public/assets/css/app.css';
$css = file_get_contents($cssFile);

// Reemplazar .auth-page
$oldAuthPage = '/\.auth-page\s*\{[^}]*\}/s';
$newAuthPage = <<<'CSS'
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg);
    background-image:
        linear-gradient(var(--color-bg-grid) 1px, transparent 1px),
        linear-gradient(90deg, var(--color-bg-grid) 1px, transparent 1px);
    background-size: 40px 40px;
    padding: var(--space-4) var(--space-3);
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
}
CSS;

$css = preg_replace($oldAuthPage, $newAuthPage, $css, 1);

// Reemplazar .auth-card
$oldAuthCard = '/\.auth-card\s*\{[^}]*\}/s';
$newAuthCard = <<<'CSS'
.auth-card {
    background: var(--glass-bg);
    backdrop-filter: blur(var(--glass-blur));
    -webkit-backdrop-filter: blur(var(--glass-blur));
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    width: 100%;
    max-width: 420px;
    box-shadow: var(--shadow-lg), var(--shadow-neu);
    position: relative;
    z-index: 1;
    animation: fadeIn 0.5s ease-out;
    margin: auto;
}
CSS;

$css = preg_replace($oldAuthCard, $newAuthCard, $css, 1);

// Reemplazar .auth-card h1 (más compacto)
$oldH1 = '/\.auth-card h1\s*\{[^}]*\}/s';
$newH1 = <<<'CSS'
.auth-card h1 {
    font-size: 17px;
    margin-bottom: var(--space-3);
    text-align: center;
    color: var(--color-text);
    font-weight: 500;
}
CSS;

$css = preg_replace($oldH1, $newH1, $css, 1);

// Reemplazar .auth-card form (gap más pequeño)
$oldForm = '/\.auth-card form\s*\{[^}]*\}/s';
$newForm = <<<'CSS'
.auth-card form {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}
CSS;

$css = preg_replace($oldForm, $newForm, $css, 1);

// Reemplazar .auth-card label (gap más pequeño)
$oldLabel = '/\.auth-card label\s*\{[^}]*\}/s';
$newLabel = <<<'CSS'
.auth-card label {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
CSS;

$css = preg_replace($oldLabel, $newLabel, $css, 1);

// Reemplazar .auth-card input (padding más pequeño)
$oldInput = '/\.auth-card input\s*\{[^}]*\}/s';
$newInput = <<<'CSS'
.auth-card input {
    padding: var(--space-2) var(--space-3);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    color: var(--color-text);
    outline: none;
    transition: all 0.25s;
    box-shadow: var(--shadow-neu-inset);
    font-size: 14px;
    text-transform: none;
    letter-spacing: normal;
    font-weight: 400;
}
CSS;

$css = preg_replace($oldInput, $newInput, $css, 1);

// Reemplazar .auth-card button[type="submit"] (padding más pequeño)
$oldButton = '/\.auth-card button\[type="submit"\]\s*\{[^}]*\}/s';
$newButton = <<<'CSS'
.auth-card button[type="submit"] {
    padding: var(--space-2) var(--space-4);
    background: var(--gradient-primary);
    color: #1a3a0f;
    border-radius: var(--radius-md);
    font-weight: 700;
    font-size: 14px;
    transition: all 0.25s;
    margin-top: var(--space-1);
    box-shadow: var(--shadow-glow-primary);
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
}
CSS;

$css = preg_replace($oldButton, $newButton, $css, 1);

// Reemplazar .auth-card__logo (más compacto)
$oldLogo = '/\.auth-card__logo\s*\{[^}]*\}/s';
$newLogo = <<<'CSS'
.auth-card__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
    font-size: 20px;
    font-weight: 700;
    color: var(--color-text);
    letter-spacing: -0.5px;
}
CSS;

$css = preg_replace($oldLogo, $newLogo, $css, 1);

// Reemplazar .auth-card__footer (más compacto)
$oldFooter = '/\.auth-card__footer\s*\{[^}]*\}/s';
$newFooter = <<<'CSS'
.auth-card__footer {
    text-align: center;
    margin-top: var(--space-3);
    font-size: 12px;
    color: var(--color-text-soft);
}
CSS;

$css = preg_replace($oldFooter, $newFooter, $css, 1);

file_put_contents($cssFile, $css);
echo "[OK] app.css actualizado\n";

// ============================================================
// 2. Arreglar register.php
// ============================================================
$registerFile = $base . '/app/Views/auth/register.php';
$register = file_get_contents($registerFile);

// Buscar el bloque del captcha y reemplazarlo
$oldCaptchaPattern = '/<label>\s*Captcha\s*<div[^>]*>.*?<\/label>/s';

$newCaptchaBlock = <<<'HTML'
<label>
                    Captcha
                    <div style="display:flex; gap:var(--space-2); align-items:center;">
                        <img src="/filemanager/captcha" alt="Captcha"
                             style="border-radius:var(--radius-sm); border:1px solid var(--color-border); cursor:pointer; flex-shrink:0; height:44px;"
                             onclick="this.src='/filemanager/captcha?'+Date.now()"
                             title="Clic para recargar">
                        <input type="text" name="captcha" required maxlength="5" autocomplete="off"
                               style="flex:1; min-width:0; text-transform:uppercase; letter-spacing:4px; font-weight:700; text-align:center; font-size:16px; padding:var(--space-2) var(--space-3); background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-md); color:var(--color-text); outline:none;"
                               placeholder="— — — — —">
                    </div>
                </label>
HTML;

$register = preg_replace($oldCaptchaPattern, $newCaptchaBlock, $register, 1);

// Reducir el textarea del mensaje
$register = str_replace(
    'rows="3" style="padding:var(--space-3) var(--space-4);',
    'rows="2" style="padding:var(--space-2) var(--space-3);',
    $register
);

// Reducir el select del departamento
$register = str_replace(
    'style="padding:var(--space-3) var(--space-4); background:var(--color-surface);',
    'style="padding:var(--space-2) var(--space-3); background:var(--color-surface);',
    $register
);

file_put_contents($registerFile, $register);
echo "[OK] register.php actualizado\n";

echo "\nListo. Recarga /register con Ctrl+F5.\n";
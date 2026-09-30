<?php

// Debug completo del captcha
header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG CAPTCHA ===\n\n";

// 1. Info del servidor
echo "--- SERVIDOR ---\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n\n";

// 2. GD
echo "--- GD ---\n";
echo "GD cargado: " . (extension_loaded('gd') ? 'SÍ' : 'NO') . "\n";
if (extension_loaded('gd')) {
    $info = gd_info();
    echo "GD Version: " . ($info['GD Version'] ?? 'N/A') . "\n";
    echo "PNG Support: " . (($info['PNG Support'] ?? false) ? 'SÍ' : 'NO') . "\n";
}
echo "\n";

// 3. Sesión
echo "--- SESIÓN ---\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "\n";

// 4. Probar generar captcha
echo "--- GENERAR CAPTCHA ---\n";
try {
    // Cargar la clase Captcha
    require __DIR__ . '/../app/Core/Captcha.php';
    
    $code = \App\Core\Captcha::generateCode();
    echo "Código generado: $code\n";
    
    $_SESSION['captcha_code'] = $code;
    echo "Guardado en sesión\n";
    
    $binary = \App\Core\Captcha::generateImage($code);
    echo "Imagen generada: " . strlen($binary) . " bytes\n";
    
    // Guardar imagen en disco para inspección
    file_put_contents(__DIR__ . '/../storage/captcha_test.png', $binary);
    echo "Guardada en storage/captcha_test.png\n";
    
    // Guardar código en archivo
    file_put_contents(__DIR__ . '/../storage/captcha_code.txt', $code);
    echo "Código guardado en storage/captcha_code.txt\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
echo "\n";

// 5. Info de headers
echo "--- HEADERS ---\n";
echo "headers_sent: " . (headers_sent($file, $line) ? "SÍ en $file:$line" : 'NO') . "\n";
echo "ob_get_level: " . ob_get_level() . "\n";
echo "output_buffering: " . ini_get('output_buffering') . "\n";
echo "\n";

// 6. Probar enviar imagen
echo "--- PROBAR ENVÍO DE IMAGEN ---\n";
echo "Si ves esto, la imagen NO se envió.\n";
echo "La URL /captcha debería devolver una imagen, no este debug.\n";
echo "\n";

echo "=== FIN DEBUG ===\n";
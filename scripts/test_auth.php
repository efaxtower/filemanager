<?php

session_start();

require __DIR__ . '/../app/Core/Database.php';
// ... el resto igual
require __DIR__ . '/../app/Auth/UserRepository.php';
require __DIR__ . '/../app/Auth/Auth.php';

use App\Auth\Auth;

$auth = new Auth();

echo "=== Registrar usuario 'juan' ===\n";
try {
    $id = $auth->register('juan', 'password123');
    echo "OK: id = $id\n";
} catch (InvalidArgumentException $e) {
    echo "FALLO: {$e->getMessage()}\n";
}

echo "\n=== Intentar registrar 'juan' otra vez ===\n";
try {
    $auth->register('juan', 'otra12345');
    echo "FALLO: deberia haber rechazado\n";
} catch (InvalidArgumentException $e) {
    echo "OK: {$e->getMessage()}\n";
}

echo "\n=== Intentar registrar username invalido ===\n";
try {
    $auth->register('juan pérez', 'password123');
    echo "FALLO: deberia haber rechazado\n";
} catch (InvalidArgumentException $e) {
    echo "OK: {$e->getMessage()}\n";
}

echo "\n=== Login correcto ===\n";
$ok = $auth->login('juan', 'password123');
echo $ok ? "OK: logueado\n" : "FALLO\n";

echo "\n=== currentUser ===\n";
$u = $auth->currentUser();
echo $u === null ? "No hay usuario\n" : "Usuario: {$u['username']} (id={$u['id']})\n";

echo "\n=== Login con contrasena incorrecta ===\n";
$ok = $auth->login('juan', 'mal');
echo $ok ? "FALLO: deberia haber fallado\n" : "OK: rechazado\n";

echo "\n=== Logout ===\n";
$auth->logout();
echo "check: " . ($auth->check() ? 'true' : 'false') . "\n";
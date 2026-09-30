<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Auth/UserRepository.php';

use App\Core\Database;

$username = 'test';          // ← usuario a resetear
$newPassword = 'password123'; // ← nueva contraseña

$pdo = Database::getConnection();

$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
$stmt->execute([$hash, $username]);

echo "Contraseña de '$username' cambiada a: $newPassword\n";
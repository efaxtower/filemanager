<?php
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$pdo = Database::getConnection();
echo "Conexiòn establecida exitoxamente\n";

$version = $pdo->query('SELECT VERSION() AS v')->fetch();
echo "Version de MySQL/MariaDB:" . $version['v'] . "\n";
?>

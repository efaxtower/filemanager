<?php

require __DIR__ . '/../app/Filesystem/Storage.php';

use App\Filesystem\Storage;

$storage = new Storage();

$base = __DIR__ . '/../storage';
$testFolder = $base . '/users/999_test';

echo "=== Crear carpeta de prueba ===\n";
try {
    $storage->createFolder($testFolder);
    echo "OK: $testFolder\n";
} catch (RuntimeException $e) {
    echo "FALLO: {$e->getMessage()}\n";
}

echo "\n=== Escribir archivo ===\n";
try {
    $bytes = $storage->writeFile($testFolder . '/nota.txt', "Hola mundo\n");
    echo "OK: $bytes bytes escritos\n";
} catch (RuntimeException $e) {
    echo "FALLO: {$e->getMessage()}\n";
}

echo "\n=== Intentar salir del storage (path traversal) ===\n";
try {
    $storage->createFolder($base . '/../../etc/hack');
    echo "FALLO: deberia haber rechazado\n";
} catch (RuntimeException $e) {
    echo "OK: {$e->getMessage()}\n";
}

echo "\n=== Limpiar ===\n";
$file = $testFolder . '/nota.txt';
if (file_exists($file)) unlink($file);
if (is_dir($testFolder)) rmdir($testFolder);
echo "Limpio\n";
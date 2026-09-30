<?php

require __DIR__ . '/../app/Filesystem/Storage.php';

use App\Filesystem\Storage;

$storage = new Storage();
$base = __DIR__ . '/../storage';
$testFolder = $base . '/users/998_test';

// Crear estructura
$storage->createFolder($testFolder);
$storage->createFolder($testFolder . '/sub');
$storage->writeFile($testFolder . '/a.txt', 'A');
$storage->writeFile($testFolder . '/sub/b.txt', 'B');

echo "=== Estructura creada ===\n";
echo "a.txt existe: " . ($storage->exists($testFolder . '/a.txt') ? 'si' : 'no') . "\n";
echo "sub/b.txt existe: " . ($storage->exists($testFolder . '/sub/b.txt') ? 'si' : 'no') . "\n";

echo "\n=== Mover a.txt a renamed.txt ===\n";
$storage->move($testFolder . '/a.txt', $testFolder . '/renamed.txt');
echo "a.txt existe: " . ($storage->exists($testFolder . '/a.txt') ? 'si' : 'no') . "\n";
echo "renamed.txt existe: " . ($storage->exists($testFolder . '/renamed.txt') ? 'si' : 'no') . "\n";

echo "\n=== Borrar carpeta completa (recursivo) ===\n";
$storage->delete($testFolder);
echo "Carpeta existe: " . ($storage->exists($testFolder) ? 'si' : 'no') . "\n";
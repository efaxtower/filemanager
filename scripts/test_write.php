<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Filesystem/NodeRepository.php';

use App\Filesystem\NodeRepository;

$repo = new NodeRepository();
$userId = 1;

// Crear estructura de prueba
echo "=== Crear estructura ===\n";
$folderId = $repo->create($userId, null, 'test_folder', 'folder');
$fileId = $repo->create($userId, $folderId, 'archivo.txt', 'file', 2048, 'text/plain');
echo "Carpeta id: $folderId, Archivo id: $fileId\n";

// Renombrar
echo "\n=== Renombrar 'archivo.txt' a 'renombrado.txt' ===\n";
$ok = $repo->rename($fileId, $userId, 'renombrado.txt');
echo $ok ? "OK renombrado\n" : "FALLO\n";

$file = $repo->findById($fileId, $userId);
echo "Nombre actual: {$file['name']}\n";

// Intentar renombrar algo que no existe
echo "\n=== Renombrar algo inexistente ===\n";
$ok = $repo->rename(99999, $userId, 'no_existe.txt');
echo $ok ? "FALLO, deberia devolver false\n" : "OK, devolvio false\n";

// Probar cascada
echo "\n=== Bytes antes de borrar ===\n";
echo "Total: " . $repo->getUsedBytes($userId) . " bytes\n";

echo "\n=== Borrar carpeta (cascada) ===\n";
$ok = $repo->delete($folderId, $userId);
echo $ok ? "OK borrado\n" : "FALLO\n";

echo "\n=== Verificar cascada: el archivo hijo debe haber desaparecido ===\n";
$file = $repo->findById($fileId, $userId);
echo $file === null ? "OK, cascada funciono\n" : "FALLO, archivo aun existe\n";

echo "\n=== Bytes despues de borrar ===\n";
echo "Total: " . $repo->getUsedBytes($userId) . " bytes\n";
<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Filesystem/PathNotFoundException.php';
require __DIR__ . '/../app/Filesystem/ResolvedPath.php';
require __DIR__ . '/../app/Filesystem/PathResolver.php';

use App\Filesystem\PathResolver;
use App\Filesystem\PathNotFoundException;

$resolver = new PathResolver();

echo "=== Raiz ===\n";
$r = $resolver->resolve(1, '/');
echo "logicalPath:  {$r->logicalPath}\n";
echo "physicalPath: {$r->physicalPath}\n";
echo "nodeId:       " . var_export($r->nodeId, true) . "\n";
echo "type:         " . var_export($r->type, true) . "\n";

echo "\n=== Ruta inexistente ===\n";
try {
    $resolver->resolve(1, '/no/existe.txt');
    echo "FALLO: no lanzo excepcion\n";
} catch (PathNotFoundException $e) {
    echo "OK: {$e->getMessage()}\n";
}

echo "\n=== Ruta invalida ===\n";
try {
    $resolver->resolve(1, '/../etc/passwd');
    echo "FALLO: no lanzo excepcion\n";
} catch (PathNotFoundException $e) {
    echo "OK: {$e->getMessage()}\n";
}
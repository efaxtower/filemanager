<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Filesystem/NodeRepository.php';

use App\Filesystem\NodeRepository;

$repo = new NodeRepository();

echo "=== findChildren (raiz del user 1) ===\n";
$children = $repo->findChildren(1, null);
echo "Total: " . count($children) . " nodos\n";
foreach ($children as $c) {
    echo "  [{$c['type']}] {$c['name']}\n";
}

echo "\n=== findById (id=1, user=1) ===\n";
$n = $repo->findById(1, 1);
echo $n === null ? "No existe\n" : "Encontrado: {$n['name']}\n";

echo "\n=== exists (user=1, raiz, 'documentos') ===\n";
$existe = $repo->exists(1, null, 'documentos');
echo $existe ? "Existe\n" : "No existe\n";

echo "\n=== getUsedBytes (user=1) ===\n";
echo "Bytes usados: " . $repo->getUsedBytes(1) . "\n";
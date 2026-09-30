<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Filesystem/NodeRepository.php';

use App\Filesystem\NodeRepository;

$repo = new NodeRepository();

echo "=== Crear carpeta 'documentos' en raiz ===\n";
$id = $repo->create(1, null, 'documentos', 'folder');
echo "Creada con id: $id\n";

echo "\n=== Crear archivo 'nota.txt' dentro ===\n";
$idFile = $repo->create(1, $id, 'nota.txt', 'file', 1024, 'text/plain');
echo "Creado con id: $idFile\n";

echo "\n=== Listar raiz ===\n";
foreach ($repo->findChildren(1, null) as $c) {
    echo "  [{$c['type']}] {$c['name']} (id={$c['id']})\n";
}

echo "\n=== Listar dentro de 'documentos' ===\n";
foreach ($repo->findChildren(1, $id) as $c) {
    echo "  [{$c['type']}] {$c['name']} (size={$c['size_bytes']})\n";
}

echo "\n=== Bytes usados ===\n";
echo "Total: " . $repo->getUsedBytes(1) . " bytes\n";
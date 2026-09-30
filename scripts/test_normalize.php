<?php

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Filesystem/PathNotFoundException.php';
require __DIR__ . '/../app/Filesystem/PathResolver.php';

use App\Filesystem\PathResolver;
use App\Filesystem\PathNotFoundException;

$resolver = new PathResolver();

$casos = [
    '/'                          => [],
    '/documentos'                => ['documentos'],
    '/documentos/nota.txt'       => ['documentos', 'nota.txt'],
    'documentos/nota.txt'        => ['documentos', 'nota.txt'],
    '/documentos/'               => ['documentos'],
    '//documentos//nota.txt//'   => ['documentos', 'nota.txt'],
];

foreach ($casos as $input => $esperado) {
    $resultado = $resolver->normalize($input);
    $ok = $resultado === $esperado ? 'OK' : 'FALLO';
    echo "[$ok] '$input' → " . json_encode($resultado) . "\n";
}

$invalidos = ['/../etc/passwd', '/docs/../secreto', '/docs/./nota'];

foreach ($invalidos as $input) {
    try {
        $resolver->normalize($input);
        echo "[FALLO] '$input' no lanzó excepción\n";
    } catch (PathNotFoundException $e) {
        echo "[OK] '$input' rechazado: {$e->getMessage()}\n";
    }
}
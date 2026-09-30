<?php

namespace App\Filesystem;

final class ResolvedPath
{
    public function __construct(
        public readonly ?int $nodeId,
        public readonly ?int $parentId,
        public readonly array $segments,
        public readonly string $logicalPath,
        public readonly string $physicalPath,
        public readonly ?string $name,
        public readonly ?string $type,
    ) {}
}
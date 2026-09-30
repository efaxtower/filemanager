<?php

return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'filemanager',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'storage' => [
        'path'              => __DIR__ . '/../storage',
        'users_dir'         => 'users',
        'shared_dir'        => 'shared',
        'max_upload_bytes'  => 6 * 1024 * 1024,
        'default_quota_bytes' => 15 * 1024 * 1024 * 1024,
    ],
    'app' => [
        'name'  => 'FileManager',
        'debug' => true,
    ],
];
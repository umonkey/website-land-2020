<?php

$settings = [
    // Slim settings.
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header

    // Application settings.
    'admin' => [
        'allowed_roles' => ['admin'],
    ],

    'dsn' => [
        'name' => 'sqlite:' . __DIR__ . '/../data/database.sqlite3',
    ],

    'files' => [
        'path' => __DIR__ . "/../data/files",
        'dmode' => 0770,
        'fmode' => 0660,
    ],

    'templates' => [
        'template_path' => [
            __DIR__ . '/../themes/land/templates',
            __DIR__ . '/../vendor/umonkey/ufw1/templates',
        ],
        'defaults' => [
        ]
    ],

    'logger' => [
        'path' => __DIR__ . '/../tmp/php.log.%Y%m%d',
        'symlink' => __DIR__ . '/../tmp/php.log',
    ],

    'nodes_idx' => [
        'user' => [
            'email',
        ],
        'picture' => [
            'author',
            'status',
        ],
    ],

    'node_forms' => include __DIR__ . '/node-forms.php',

    'S3' => [
        'bucket' => 'umonkey-land',
        'bucket_region' => 'ru-central1',
        'acl' => 'public-read',
        'access_key' => null,
        'secret_key' => null,
        'endpoint' => 'storage.yandexcloud.net',
        'console' => 'https://console.cloud.yandex.ru/folders/b1gbtbv153es58sd3u6c/storage/bucket/umonkey-land',
    ],

    'taskq' => [
        'ping_url' => 'http://taskq.local/ping.php?url=http://land.local/taskq/list',
        'exec_pattern' => 'http://land.local/taskq/%u/run',
    ],

    'telega' => [
        'bot_id' => null,
        'chat_id' => null,
        'proxy' => null,
    ],

    'thumbnails' => [
        'large' => [
            'width' => 1000,
            'height' => 1000,
        ],
        'medium' => [
            'width' => 500,
            'sharpen' => true,
            'from' => 'large',
        ],
        'small' => [
            'width' => 200,
            'sharpen' => true,
            'from' => 'medium',
        ],
    ],
];

// Amend passwords in a non-tracker file.
if (file_exists($fn = __DIR__ . '/../local-settings.php')) {
    include $fn;
}

return ['settings' => $settings];

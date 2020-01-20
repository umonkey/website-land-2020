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
        'name' => 'sqlite:' . __DIR__ . '/../var/database/database.sqlite3',
    ],

    'files' => [
        'path' => __DIR__ . "/../var/files",
        'dmode' => 0770,
        'fmode' => 0660,
    ],

    'logger' => [
        'path' => __DIR__ . '/../var/logs/php.log.%Y%m%d',
        'symlink' => __DIR__ . '/../var/logs/php.log',
    ],

    'nodes_idx' => [
        'user' => [
            'email',
        ],
        'picture' => [
            'author',
            'status',
        ],
        'wiki' => [
            'url',
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
        'auto_upload' => false,
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

    'templates' => [
        'template_path' => [
            __DIR__ . '/../themes/land/templates',
            __DIR__ . '/../vendor/umonkey/ufw1/templates',
        ],
        'strings' => include __DIR__ . '/strings.php',
    ],

    'thumbnails' => [
        'large' => [
            'width' => 1000,
            'height' => 1000,
        ],
        'large_webp' => [
            'width' => 1000,
            'height' => 1000,
            'format' => 'webp',
        ],
        'medium' => [
            'width' => 500,
            'sharpen' => true,
            'from' => 'large',
        ],
        'medium_webp' => [
            'width' => 500,
            'sharpen' => true,
            'from' => 'large',
            'format' => 'webp',
        ],
        'small' => [
            'width' => 200,
            'sharpen' => true,
            'from' => 'medium',
        ],
        'small_webp' => [
            'width' => 200,
            'sharpen' => true,
            'from' => 'medium',
            'format' => 'webp',
        ],
    ],

    'wiki' => [
        'home_page' => 'Введение',
        'reader_roles' => ['nobody', 'admin'],
        'editor_roles' => ['admin'],
        'default_author' => 'Фрунзе Владимир',
    ],
];

$files = [
    __DIR__ . '/../settings.php',
    __DIR__ . '/../settings.' . $_ENV['APP_ENV'] . '.php',
    __DIR__ . '/../settings.local.php',
];

// Amend passwords in a non-tracker file.
foreach ($files as $fn) {
    if (file_exists($fn)) {
        include $fn;
    }
}

return ['settings' => $settings];

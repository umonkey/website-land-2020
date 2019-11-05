<?php

$config = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        'templates' => [
            'template_path' => __DIR__ . '/../templates',
            'defaults' => [
            ]
        ],

        'logger' => [
            'path' => __DIR__ . '/../tmp/php.log.%Y%m%d',
            'symlink' => __DIR__ . '/../tmp/php.log',
        ],

        'files' => [
            'path' => __DIR__ . "/../data/files",
            'dmode' => 0770,
            'fmode' => 0660,
        ],

        'dsn' => [
            'name' => 'sqlite:' . __DIR__ . '/../data/database.sqlite3',
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

        'S3' => [
            'bucket' => 'umonkey-land',
            'bucket_region' => 'ru-central1',
            'acl' => 'public-read',
            'access_key' => null,
            'secret_key' => null,
            'endpoint' => 'storage.yandexcloud.net',
            'console' => 'https://console.cloud.yandex.ru/folders/b1gbtbv153es58sd3u6c/storage/bucket/umonkey-land',
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

        'taskq' => [
            'ping_url' => 'http://taskq.local/ping.php?url=http://land.local/taskq/list',
            'exec_pattern' => 'http://land.local/taskq/%u/run',
        ],

        'admin' => [
            'allowed_roles' => ['admin'],
        ],

        'node_forms' => [
            'blog' => [
                'title' => 'Заметка',
                'description' => 'запись в блоге, можно комментировать',
                'new_title' => 'Добавление заметки',
                'edit_title' => 'Редактирование заметки',
                'edit_roles' => ['admin'],
                'fields' => [
                    'name' => [
                        'label' => 'Заголовок',
                        'type' => 'textline',
                        'required' => true,
                    ],
                    'text' => [
                        'type' => 'textarea',
                        'rows' => 10,
                        'required' => true,
                        'class' => 'markdown',
                        'help' => 'Можно использовать <a href="http://ilfire.ru/kompyutery/shpargalka-po-sintaksisu-markdown-markdaun-so-vsemi-samymi-populyarnymi-tegami/" target="blank">форматирование Markdown</a>.',
                        'buttons' => ['upload' => 'fas fa-image'],
                    ],
                    'url' => [
                        'label' => 'URL',
                        'type' => 'textline',
                        'required' => false,
                    ],
                    'published' => [
                        'type' => 'checkbox',
                        'label' => 'опубликовать заметку',
                    ],
                    'deleted' => [
                        'type' => 'checkbox',
                        'label' => 'удалить заметку',
                    ],
                ],
            ],
            'article' => [
                'title' => 'Статья',
                'description' => 'произвольные текстовые блоки для вывода на сайте',
                'edit_title' => 'Редактирование статьи',
                'fields' => [
                    'name' => [
                        'label' => 'Заголовок',
                        'type' => 'textline',
                        'required' => true,
                    ],
                    'text' => [
                        'label' => 'Текст',
                        'type' => 'textarea',
                        'rows' => 10,
                        'required' => true,
                        'class' => 'markdown',
                        'help' => 'Можно использовать <a href="http://ilfire.ru/kompyutery/shpargalka-po-sintaksisu-markdown-markdaun-so-vsemi-samymi-populyarnymi-tegami/" target="blank">форматирование Markdown</a>.',
                    ],
                    'url' => [
                        'label' => 'URL',
                        'type' => 'textline',
                        'required' => false,
                    ],
                    'published' => [
                        'type' => 'checkbox',
                        'label' => 'опубликовать статью',
                    ],
                    'deleted' => [
                        'type' => 'checkbox',
                        'label' => 'удалить статью',
                    ],
                ],
            ],  // article
            'file' => [
                'edit_title' => 'Редактирование файла',
                'fields' => [
                    'name' => [
                        'label' => 'Название файла',
                        'type' => 'textline',
                        'required' => true,
                    ],
                    'kind' => [
                        'label' => 'Тип содержимого',
                        'type' => 'select',
                        'options' => [
                            'photo' => 'фотография',
                            'video' => 'видео',
                            'audio' => 'звук',
                            'other' => 'другое',
                        ],
                    ],
                    'mime_type' => [
                        'label' => 'Тип MIME',
                        'type' => 'textline',
                        'required' => true,
                    ],
                    'files' => [
                        'label' => 'Варианты файла',
                        'type' => 'fileparts',
                    ],
                    'published' => [
                        'type' => 'hidden',
                    ],
                    'deleted' => [
                        'type' => 'checkbox',
                        'label' => 'удалить файл',
                    ],
                ],
            ],
            'user' => [
                'new_title' => 'Добавление пользователя',
                'edit_title' => 'Редактирование профиля пользователя',
                'fields' => [
                    'name' => [
                        'label' => 'Фамилия, имя',
                        'type' => 'textline',
                        'required' => true,
                        'placeholder' => 'Сусанин Иван',
                    ],
                    'email' => [
                        'label' => 'Email',
                        'type' => 'textline',
                        'required' => true,
                    ],
                    'phone' => [
                        'label' => 'Номер телефона',
                        'type' => 'textline',
                    ],
                    'role' => [
                        'label' => 'Роль в работе сайта',
                        'type' => 'select',
                        'options' => [
                            'nobody' => 'никто',
                            'user' => 'пользователь',
                            'editor' => 'редактор',
                            'admin' => 'администратор',
                        ],
                    ],
                    'published' => [
                        'label' => 'разрешить доступ',
                        'type' => 'checkbox',
                    ],
                ],
            ],
        ],  // node_forms
    ],
];

// Amend passwords in a non-tracker file.
if (file_exists($fn = __DIR__ . '/settings-local.php'))
    require $fn;

return $config;

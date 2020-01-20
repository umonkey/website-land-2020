<?php

$_ENV['APP_ENV'] = getenv('APP_ENV') ?: 'dev';
$_ENV['APP_DEBUG'] = $_ENV['APP_ENV'] == 'dev';

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/functions.php';

// Instantiate the app
$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/dependencies.php';

// Register middleware
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';

// TODO: move to app!
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($container) {
    if (!(error_reporting() & $errno))
        return false;

    $logger = $container->get('logger');

    $logger->error('Unhandled error: {str}, file {file} line {line}', [
        'str' => $errstr,
        'file' => $errfile,
        'line' => $errline,
    ]);

    throw new \RuntimeException($errstr);
});

return $app;

<?php

ini_set("display_errors", 1);

umask(0007);

if (version_compare(PHP_VERSION, '5.5.0') < 0) {
    header("HTTP/1.0 503 Service Unavailable");
    header("Content-Type: text/plain; charset=utf-8");
    die("Для работы сайта нужен PHP версии 5.5 или более поздней.");
}

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }

    // Do not route static paths to the application.
    if (preg_match('@^/(photo|css|js)/@', $url["path"]))
        return false;
}

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e !== null) {
        $text = var_export($e, true);
        error_log('SHUTDOWN: ' . $text);
    }
});

require __DIR__ . '/../src/bootstrap.php';

// Run app
$app->run();

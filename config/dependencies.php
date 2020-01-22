<?php

/**
 * Dependency container setup.
 **/

declare(strict_types=1);

use App\Util;
use Psr\Container\ContainerInterface;

$container = $app->getContainer();

Util::containerSetup($container);
Util::installErrorHandler($container);

$container['kdpv'] = function (ContainerInterface $c) {
    $settings = $c->get('settings')['kdpv'];
    return new \App\Services\NodePictureService($settings);
};

$container['errorHandler'] = function (ContainerInterface $c) {
    return function ($request, $response, $e) use ($c) {
        $h = new App\Controllers\ErrorController($c);
        return $h($request, $response, ['exception' => $e]);
    };
};

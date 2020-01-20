<?php
/**
 * Dependency container setup.
 **/

use App\Util;

$container = $app->getContainer();

Util::containerSetup($container);
Util::installErrorHandler($container);

$container['kdpv'] = function ($c) {
    return new \App\Services\NodePictureService($c);
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $e) use ($c) {
        $h = new \App\Handlers\Error($c);
        return $h($request, $response, ['exception' => $e]);
    };
};

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

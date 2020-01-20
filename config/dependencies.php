<?php
/**
 * Dependency container setup.
 **/

$container = $app->getContainer();

\Ufw1\Util::containerSetup($container);

$container['kdpv'] = function ($c) {
    return new \App\Services\NodePictureService($c);
};

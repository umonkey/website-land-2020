<?php

use Slim\Http\Request;
use Slim\Http\Response;

\App\Handlers\Account::setupRoutes($app);
\App\Handlers\Admin::setupRoutes($app);
\App\Handlers\TaskQ::setupRoutes($app);
\App\Handlers\Wiki::setupRoutes($app);
\Ufw1\Handlers\Files::setupRoutes($app);
\Ufw1\Util::installSearch($app);

$app->get ('/',                      '\App\Controllers\Home:index');
$app->get ('/node/{id:[0-9]+}/kdpv', '\App\Controllers\NodePictureController:index');

$app->get ('/sitemap.xml', '\App\Handlers\Sitemap');
$app->post('/admin/upload', '\Ufw1\Handlers\Wiki:onUpload');

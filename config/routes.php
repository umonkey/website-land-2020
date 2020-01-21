<?php
ini_set('display_errors', 1);

use Slim\Http\Request;
use Slim\Http\Response;

use App\Util;

// TODO: move to $app->installThisAndThat().
Util::installAccount($app);
Util::installAdmin($app);
Util::installFiles($app);
Util::installSearch($app);
Util::installTaskQ($app);
Util::installWiki($app);

$app->get('/', '\App\Controllers\HomeController:index');
$app->post('/admin/upload', '\Ufw1\Handlers\Wiki:onUpload');
$app->get('/node/{id:[0-9]+}/kdpv', '\App\Controllers\NodePictureController:index');

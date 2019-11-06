<?php

use Slim\Http\Request;
use Slim\Http\Response;

\App\Handlers\Admin::setupRoutes($app);
\App\Handlers\TaskQ::setupRoutes($app);

$app->get ('/', '\App\Handlers\Home:onHome');
$app->get ('/blog', '\App\Handlers\Home:onBlog');
$app->get ('/blog/', '\App\Handlers\Home:onStripSlash');

$app->any ('/register', '\App\Handlers\Account:onRegister');
$app->any ('/profile', '\App\Handlers\Account:onProfile');
$app->get ('/files', '\App\Handlers\Files:onGetRecent');
$app->get ('/files/recent.json', '\App\Handlers\Files:onGetRecentJson');
$app->get ('/files/{id:[0-9]+}', '\App\Handlers\Files:onShowFile');
$app->get ('/files/{id:[0-9]+}/download', '\App\Handlers\Files:onDownload');
$app->post('/login', '\App\Handlers\Account:onLogin');
$app->get ('/logout', '\App\Handlers\Account:onLogout');
$app->get ('/f/{name}', '\App\Handlers\Storage:onGetItem');
$app->get ('/search', \App\Handlers\Search::class . ':onGet');
$app->get ('/search/log', \App\Handlers\Search::class . ':onLog');
$app->get ('/sitemap.xml', '\App\Handlers\Sitemap');
$app->get ('/stats', '\App\Handlers\Home:onStats');
$app->get ('/node/{id:[0-9]+}', '\App\Handlers\Node:onShow');
$app->post('/node/{id:[0-9]+}', '\App\Handlers\Node:onSave');
$app->get ('/node/{id:[0-9]+}/update-thumbnail', '\App\Handlers\Node:onUpdateThumbnail');
$app->get ('/node/{id:[0-9]+}/upload-s3', '\App\Handlers\Node:onUploadS3');
$app->get ('/node/{id:[0-9]+}/download/{code:.*}', '\App\Handlers\Node:onDownload');
$app->post('/node/save', '\App\Handlers\Node:onSave');
$app->post('/admin/upload', '\Ufw1\Handlers\Wiki:onUpload');

$app->get ('/{url:.*}', '\App\Handlers\Nodes:onDefault');

<?php

use Slim\Http\Request;
use Slim\Http\Response;

\App\Handlers\Account::setupRoutes($app);
\App\Handlers\Admin::setupRoutes($app);
\App\Handlers\TaskQ::setupRoutes($app);
\App\Handlers\Wiki::setupRoutes($app);
\Ufw1\Handlers\Files::setupRoutes($app);

$app->get ('/', '\App\Handlers\Home:onHome');
$app->get ('/articles', '\App\Handlers\Articles:onList');
$app->get ('/blog', '\App\Handlers\Blog:onBlog');
$app->get ('/blog/', '\App\Handlers\Home:onStripSlash');

$app->get ('/files', '\App\Handlers\Files:onGetRecent');
$app->get ('/files/recent.json', '\App\Handlers\Files:onGetRecentJson');
$app->get ('/files/{id:[0-9]+}', '\App\Handlers\Files:onShowFile');
$app->get ('/files/{id:[0-9]+}/download', '\App\Handlers\Files:onDownload');
$app->get ('/f/{name}', '\App\Handlers\Storage:onGetItem');
$app->get ('/search', '\App\Handlers\Search:onGet');
$app->get ('/search/log', '\App\Handlers\Search:onLog');
$app->get ('/sitemap.xml', '\App\Handlers\Sitemap');
$app->get ('/stats', '\App\Handlers\Home:onStats');
$app->get ('/node/{id:[0-9]+}', '\App\Handlers\Node:onShow');
$app->get ('/node/{id:[0-9]+}.html', '\App\Handlers\Node:onShow');
$app->post('/node/{id:[0-9]+}', '\App\Handlers\Node:onSave');
$app->get ('/node/{id:[0-9]+}/kdpv', '\App\Handlers\Node:onKDPV');
$app->get ('/node/{id:[0-9]+}/update-thumbnail', '\App\Handlers\Node:onUpdateThumbnail');
$app->get ('/node/{id:[0-9]+}/upload-s3', '\App\Handlers\Node:onUploadS3');
$app->post('/node/save', '\App\Handlers\Node:onSave');
$app->post('/admin/upload', '\Ufw1\Handlers\Wiki:onUpload');

<?php

use Slim\Http\Request;
use Slim\Http\Response;

\App\Handlers\Admin::setupRoutes($app);
\App\Handlers\TaskQ::setupRoutes($app);

$app->get ('/blog', '\App\Handlers\Home:onBlog');
$app->get ('/blog/', '\App\Handlers\Home:onStripSlash');

$app->get ('/cron', '\App\Handlers\Cron:onDefault');
$app->get ('/cron/push-s3', '\App\Handlers\Cron:onPushS3');

$app->get ('/catalog/pull', '\App\Handlers\Catalog:onPull');
$app->get ('/catalog/search', '\App\Handlers\Catalog:onSearch');
$app->any ('/download/{id:[0-9]+}', '\App\Handlers\Node:onDownloadFile');
$app->any ('/register', '\App\Handlers\Account:onRegister');
$app->any ('/profile', '\App\Handlers\Account:onProfile');
$app->any ('/submit', '\App\Handlers\Submit:onSubmit');
$app->get ('/my', '\App\Handlers\Home:onMy');
$app->post('/node/{id:[0-9]+}/comment', '\App\Handlers\Node:onComment');
$app->post('/node/{id:[0-9]+}/delete', '\App\Handlers\Node:onDelete');
$app->get ('/node/{id:[0-9]+}/dump', '\App\Handlers\Node:onDump');
$app->get ('/node/{id:[0-9]+}/edit', '\App\Handlers\Node:onEdit');
$app->post('/node/{id:[0-9]+}/edit', '\App\Handlers\Node:onSave');
$app->post('/node/{id:[0-9]+}/sudo', '\App\Handlers\Node:onSudo');
$app->post('/node/{id:[0-9]+}/unpublish', '\App\Handlers\Node:onUnpublish');
$app->get ('/export.json', '\App\Handlers\Export:onGetJSON');
$app->get ('/files', '\App\Handlers\Files:onGetRecent');
$app->get ('/files/{id:[0-9]+}', '\App\Handlers\Files:onShowFile');
$app->get ('/files/{id:[0-9]+}/download', '\App\Handlers\Files:onDownload');
$app->get ('/import-sales', '\App\Handlers\Node:onImportSales');
$app->get ('/files/{name:.*}', '\App\Handlers\File');
$app->post('/login', '\App\Handlers\Account:onLogin');
$app->get ('/logout', '\App\Handlers\Account:onLogout');
$app->get ('/f/{name}', '\App\Handlers\Storage:onGetItem');
$app->get ('/payout', '\App\Handlers\Home:onPayout');
$app->get ('/search', \App\Handlers\Search::class . ':onGet');
$app->get ('/search/log', \App\Handlers\Search::class . ':onLog');
$app->get ('/sitemap.xml', '\App\Handlers\Sitemap');
$app->get ('/stats', '\App\Handlers\Home:onStats');
$app->get ('/', '\App\Handlers\Home:onHome');
$app->get ('/waiting', '\App\Handlers\Home:onWaiting');
$app->get ('/rejected', '\App\Handlers\Home:onRejected');
$app->get ('/queue', '\App\Handlers\Home:onQueue');
$app->get ('/accepted', '\App\Handlers\Home:onAccepted');
$app->get ('/pictures', '\App\Handlers\Home:onManagePictures');
$app->get ('/users', '\App\Handlers\Home:onUsers');
$app->any ('/upload', '\App\Handlers\Upload:onUpload');
$app->any ('/upload/test', '\App\Handlers\Upload:onUploadTest');
$app->get ('/node/{id:[0-9]+}', '\App\Handlers\Node:onShow');
$app->post('/node/{id:[0-9]+}', '\App\Handlers\Node:onSave');
$app->get ('/node/{id:[0-9]+}/update-thumbnail', '\App\Handlers\Node:onUpdateThumbnail');
$app->get ('/node/{id:[0-9]+}/upload-s3', '\App\Handlers\Node:onUploadS3');
$app->get ('/node/{id:[0-9]+}/download/{code:.*}', '\App\Handlers\Node:onDownload');
$app->post('/node/save', '\App\Handlers\Node:onSave');

$app->get ('/{url:.*}', '\App\Handlers\Nodes:onDefault');

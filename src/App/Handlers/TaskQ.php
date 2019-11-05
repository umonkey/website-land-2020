<?php
/**
 * Task Queue, background task execution.
 *
 * @docs http://bugs.home.umonkey.net/wiki?name=%D0%9E%D1%87%D0%B5%D1%80%D0%B5%D0%B4%D1%8C+%D0%B7%D0%B0%D0%B4%D0%B0%D1%87
 *
 * @see vendor/umonkey/ufw1/src/Ufw1/Handlers/TaskQ.php
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;

class TaskQ extends \Ufw1\Handlers\TaskQ
{
    public function onTest(Request $request, Response $response, array $args)
    {
        $this->requireAdmin($request);

        $id = $this->taskq->add("test");

        return "Task {$id} added.";
    }

    protected function handleTask($action, array $payload)
    {
        if ($action == "upload-s3")
            return $this->onUploadS3($payload["id"]);

        elseif ($action == 'publish-picture')
            return $this->onPublishPicture($payload['id']);

        elseif ($action == 'export-all')
            return $this->onExportAll();

        return parent::handleTask($action, $payload);
    }

    /**
     * Выгрузка файлов ноды в S3.
     *
     * TODO: убрать, это есть в ufw.
     **/
    protected function onUploadS3($id)
    {
        $node = $this->node->get($id);

        if (empty($node)) {
            $this->logger->error("upload-s3: node {id} does not exist.", [
                "id" => $id,
            ]);

            return;
        }

        if ($node["type"] != "file") {
            $this->logger->error("upload-s3: node {id} is {type}, not a file.", [
                "id" => $id,
                "type" => $node["type"],
            ]);
        }

        $s3 = $this->container->get("S3");

        $node = $s3->uploadNodeFiles($node);
        $this->node->save($node);

        $this->logger->info("upload-s3: node {id} sent to S3.", [
            "id" => $id,
        ]);
    }

    /**
     * Публикация товара на основном сайте.
     *
     * action: publish-picture
     **/
    protected function onPublishPicture($id)
    {
        $node = $this->node->get($id);
        if (empty($node)) {
            $this->logger->debug('node {0} does not exist, cannot publish', [$id]);
            return;
        }

        if ($node['type'] != 'picture') {
            $this->logger->debug('node {0} is not a picture, cannot publish', [$id]);
            return;
        }

        $url = "https://www.artwall.ru/artman/pull/{$id}";
        $this->logger->debug('publish-picture: calling {url}', ['url' => $url]);
        $res = \App\Util::fetch($url);

        if ($res['status'] == 200) {
            $this->logger->info('publish-picture: request to publish node/{0} sent', [$id]);
        } else {
            $this->logger->error('publish-picture: request to publish node/{0} failed: {1}', [$id, $res]);
        }
    }

    /**
     * Обновление каталога на основном сайте.
     *
     * action: export-all
     **/
    protected function onExportAll()
    {
        $url = "https://www.artwall.ru/artman/pull";
        $this->logger->debug('export-all: calling {url}', ['url' => $url]);
        $res = \App\Util::fetch($url);
    }
}

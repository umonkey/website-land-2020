<?php
/**
 * Node UI.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;

class Export extends CommonHandler
{
    public function onGetJSON(Request $request, Response $response, array $args)
    {
        $id = $request->getParam('id');

        $nodes = $id
            ? $this->node->where("`type` = 'picture' AND `id` = ?", [$id])
            : $this->node->where("`type` = 'picture' ORDER BY `id` DESC");

        $authors = $this->node->where("`type` = 'user' AND `published` = 1 AND `deleted` = 0", [], function ($em) {
            return (int)$em['id'];
        });

        // Подтягиваем файлы.
        $nodes = array_map(function ($node) {
            $node['file'] = $this->node->get($node['file']);
            return $node;
        }, $nodes);

        $nodes = array_map(function ($n) use ($request) {
            $u = $this->node->get($n["author"]);
            $file = $n['file'];

            $ready = true;

            if ($u['deleted'] == 1 or $u['published'] == 0)
                $ready = false;
            if (empty($file['files']['large']))
                $ready = false;
            if ($n['status'] != 3)
                $ready = false;

            if ($ready) {
                $image = $this->getFileURL($file, 'large', $request);
                $download = $this->getFileURL($file, 'original', $request);

                $categories = array_map('intval', $n['categories']);

                return [
                    'id' => (int)$n['id'],
                    'published' => true,
                    'title' => $n['title'],
                    'description' => $n['description'],
                    'artist' => $u['name'],
                    'categories' => $categories,
                    'image' => $image,
                    'image_width' => $file['files']['large']['width'],
                    'image_height' => $file['files']['large']['height'],
                    'download' => $download,
                    'comission' => (int)$n['comission'],
                ];
            } else {
                return [
                    'id' => (int)$n['id'],
                    'published' => false,
                ];
            }
        }, $nodes);

        $this->logger->info('export: reporting {0} items to {1}', [
            count($nodes),
            $_SERVER['REMOTE_ADDR'],
        ]);

        return $response->withJSON($nodes);
    }

    protected function getFileURL(array $file, $part, $request)
    {
        if ($part == 'original')
            return $request->getUri()->getBaseUrl() . "/download/{$file['id']}";

        if (empty($file['files'][$part]['storage']))
            return null;

        switch ($file['files'][$part]['storage']) {
            case 's3':
                return $file['files'][$part]['url'];
            case 'local':
                $uri = $request->getUri();
                $base = $uri->getScheme() . "://" . $uri->getHost();
                return "{$base}/node/{$file['id']}/download/{$part}";
        }
    }
}

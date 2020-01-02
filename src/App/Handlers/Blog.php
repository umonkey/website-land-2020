<?php
/**
 * Blog home page.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;


class Blog extends CommonHandler
{
    public function onBlog(Request $request, Response $response, array $args)
    {
        $entries = $this->node->where("`type` = 'wiki' AND `deleted` = 0 AND `published` = 1 ORDER BY `created` DESC", [], function ($node) {
            $url = $node['url'] ?? '';

            if ($url == '/blog/') {
                return null;
            }

            if (0 !== strpos($url, '/blog/')) {
                return null;
            }

            return [
                'id' => $node['id'],
                'published' => (bool)(int)$node['published'],
                'created' => $node['created'],
                'title' => $node['name'],
                'summary' => $node['summary'] ?? null,
                'link' => $node['url'] ?? "/node/{$node['id']}",
            ];
        });

        $entries = array_filter($entries);

        return $this->render($request, 'blog.twig', [
            'entries' => $entries,
        ]);
    }
}

<?php
/**
 * List all articles.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;


class Articles extends CommonHandler
{
    public function onList(Request $request, Response $response, array $args)
    {
        $entries = $this->node->where("`type` = 'article' AND `deleted` = 0 AND `published` = 1", [], function ($node) {
            $url = $node['url'] ?? '';

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

        usort($entries, function ($a, $b) {
            $_a = mb_strtolower($a['title']);
            $_b = mb_strtolower($b['title']);
            return strcasecmp($_a, $_b);
        });

        return $this->render($request, 'articles.twig', [
            'entries' => $entries,
        ]);
    }
}

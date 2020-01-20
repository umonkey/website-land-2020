<?php

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;

class Nodes extends CommonHandler
{
    public function onDefault(Request $request, Response $response, array $args)
    {
        $user = $this->getUser($request);
        $role = $user['role'] ?? 'nobody';

        if (empty($args['url']))
            $this->notfound();

        $url = '/' . $args['url'];
        $url = rtrim($url, ' /');
        $key = md5($url);

        $node = $this->node->getByKey($key);
        if (empty($node))
            $this->notfound();

        if ($node['deleted'] == 1)
            $this->gone();

        if ($node['published'] == 0 and $role != 'admin')
            $this->forbidden();

        $template = "node-{$node['type']}.twig";

        return $this->render($request, $template, [
            'node' => $node,
            'edit_link' => $role == 'admin' ? "/admin/nodes/{$node['id']}/edit" : null,
        ]);
    }
}

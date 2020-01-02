<?php
/**
 * Wiki pages.
 *
 * See u/Handlers/Wiki.php
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;


class Wiki extends \Ufw1\Handlers\Wiki
{
    public function onDefault(Request $request, Response $response, array $args)
    {
        $url = '/' . $args['url'];

        $id = $this->db->fetchcell('SELECT `id` FROM `nodes_wiki_idx` WHERE `url` = ?', [$url]);
        if (empty($id)) {
            $this->notfound();
        }

        $node = $this->node->get($id);
        if (empty($node) or $node['type'] != 'wiki') {
            $this->notfound();
        }

        $name = $node['name'];
        return $this->showPageByName($request, $response, $name);
    }
}

<?php
/**
 * Most CLI actions.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;


class CLI extends CommonHandler
{
    public function onDefault(Request $request, Response $response, array $args)
    {
        switch ($args["action"]) {
            case "set-admin":
                return $this->onSetAdmin($request, $response, $args);
            default:
                die("Unknown action: {$args["action"]}.\n");
        }
    }

    public function onSetAdmin(Request $request, Response $response, array $args)
    {
        $id = $request->getParam("id");
        if (empty($id)) {
            error_log("set-param: user id not specified.");
            $nodes = $this->node->where("`type` = 'user' ORDER BY `id`");
            foreach ($nodes as $node) {
                if (!($role = @$node["role"]))
                    $role = "user";
                error_log("set-param: {$node["id"]}: {$node["email"]}, {$role}");
            }
            return;
        }

        $node = $this->node->get($id);
        if (empty($node)) {
            error_log("set-param: user {$id} not found.");
            return;
        }

        if (@$node["role"] == "admin" and $node["published"] == 1) {
            error_log("set-admin: user {$id} is already an admin.");
            return;
        }

        $node["role"] = "admin";
        $node["published"] = 1;
        $this->node->save($node);

        error_log("set-admin: user {$id} promoted to admins.");
    }
}

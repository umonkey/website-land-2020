<?php
/**
 * Custom error handler.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;


class Error extends CommonHandler
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $e = $args["exception"];

        $tpl = "error.twig";
        $status = 500;
        $data = [];
        $data["path"] = $request->getUri()->getPath();

        $stack = $e->getTraceAsString();
        $root = dirname($_SERVER["DOCUMENT_ROOT"]);
        $stack = str_replace($root . "/", "", $stack);

        // Hide arguments: passwords, etc.
        $stack = preg_replace_callback('@(\(([^()]+)\))@', function ($m) {
            if (is_numeric($m[2]))
                return $m[1];
            return '(...)';
        }, $stack);

        $data["e"] = [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'stack' => $stack,
        ];

        $xhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
        if ($xhr == "XMLHttpRequest") {
            if ($data["e"]["class"] == "Ufw1\Errors\UserFailure") {
                return $response->withJSON([
                    "message" => $e->getMessage(),
                ]);
            }

            $message = "Ошибка {$data["e"]["class"]}: {$data["e"]["message"]}";
            $message .= "\n\n" . $data["e"]["stack"];

            $this->logger->error('exception: {class}: {message}, stack: {stack}', $data['e']);

            return $response->withJSON([
                "error" => $data["e"]["class"],
                "message" => $message,
            ]);
        }

        $notify = true;

        if ($e instanceof \Ufw1\Errors\Unauthorized) {
            $tpl = "unauthorized.twig";
            $status = 401;
            $notify = false;
        }

        elseif ($e instanceof \Ufw1\Errors\Forbidden) {
            $data['intro'] = $this->node->get(55);
            $tpl = "forbidden.twig";
            $status = 403;
            $notify = false;
        }

        elseif ($e instanceof \Ufw1\Errors\NotFound) {
            $tpl = "notfound.twig";
            $status = 404;
            $notify = false;

            if ($url = $this->getRedirect($request->getUri()->getPath())) {
                return $response->withRedirect($url);
            }
        }

        if ($notify) {
            $this->logger->error('exception: {class}: {message}, stack: {stack}', $data['e']);

            $this->container->get('taskq')->add('telega', [
                'message' => "Error: {$data['e']['class']}: {$data['e']['message']}\n{$data['e']['stack']}",
            ]);
        }

        $response = $this->render($request, $tpl, $data);
        return $response->withStatus($status);
    }

    protected function getRedirect($url)
    {
        $node = $this->node->where("type = 'wiki' AND deleted = 0 AND id IN (SELECT id FROM nodes_wiki_idx WHERE url = ?)", [$url]);

        if (!empty($node)) {
            return "/wiki?name=" . urlencode($node[0]['name']);
        }
    }
}

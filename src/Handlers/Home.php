<?php
/**
 * Home page.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;


class Home extends CommonHandler
{
    public function onHome(Request $request, Response $response, array $args)
    {
        return $this->render($request, 'home.twig', [
        ]);
    }

    /**
     * Removes trailing slash from the current request.
     **/
    public function onStripSlash(Request $request, Response $response, array $args)
    {
        $url = $request->getUri();

        $next = rtrim($url->getPath(), '/');
        if ($tmp = $url->getQuery())
            $next .= '?' . $tmp;

        return $response->withRedirect($next);
    }

    public function onAdminHome(Request $request, Response $response, array $user)
    {
        return $response->withRedirect("/queue");
    }

    /**
     * Вывод списка картин на модерации.
     *
     * Фильтр нод: type=picture published=0 status=1
     **/
    public function onQueue(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        if ($user["role"] != "admin")
            return $this->forbidden();

        $nodes = $this->node->where("`type` = 'picture' AND `deleted` = 0 AND `id` IN (SELECT `id` FROM `nodes_picture_idx` WHERE `status` = 1 AND `author` IN (SELECT `id` FROM `nodes` WHERE `type` = 'user' AND `published` = 1 AND `deleted` = 0)) ORDER BY `updated` DESC");
        $nodes = $this->fillNodes($nodes);

        return $this->render($request, "queue.twig", [
            "tab" => "queue",
            "user" => $user,
            "stats" => $this->getUserStats($user),
            "nodes" => $nodes,
        ]);
    }

    /**
     * Вывод картин, принятых в работу.
     **/
    public function onAccepted(Request $request, Response $response, array $args)
    {
        $user = $this->requireAdmin($request);

        // $nodes = $this->node->where("`type` = 'picture' AND `deleted` = 0 AND `id` IN (SELECT `id` FROM `nodes_picture_idx` WHERE `status` = 3) ORDER BY `updated`");
        $nodes = $this->node->where("`type` = 'picture' AND `deleted` = 0 AND `id` IN (SELECT `id` FROM `nodes_picture_idx` WHERE `status` = 3 AND `author` IN (SELECT `id` FROM `nodes` WHERE `type` = 'user' AND `published` = 1 AND `deleted` = 0)) ORDER BY `updated`");

        $nodes = $this->fillNodes($nodes);

        return $this->render($request, "accepted.twig", [
            "tab" => "accepted",
            "user" => $user,
            "nodes" => $nodes,
        ]);
    }

    public function onMy(Request $request, Response $response, array $user)
    {
        $user = $this->requireUser($request);

        $pictures = $this->node->where("`type` = 'picture' AND `deleted` = 0 AND `id` IN (SELECT `id` FROM `nodes_picture_idx` WHERE `author` = ?) ORDER BY `created` DESC", [$user["id"]]);

        $pictures = $this->loadFiles($pictures);

        $sales = [];
        $rows = $this->db->fetch("SELECT id, SUM(qty) AS qty FROM sales GROUP BY id");
        foreach ($rows as $row)
            $sales[(int)$row["id"]] = (int)$row["qty"];

        $pictures = array_map(function ($em) use ($sales) {
            if (array_key_exists($em["id"], $sales))
                $em["payout"] = $sales[$em["id"]] * $em["comission"];
            return $em;
        }, $pictures);

        usort($pictures, function ($a, $b) {
            $_a = mb_strtolower($a['title']);
            $_b = mb_strtolower($b['title']);
            return strcmp($_a, $_b);
        });

        return $this->render($request, "home-user.twig", [
            "stats" => $this->getUserStats($user),
            "pictures" => $pictures,
            // "payout_pending" => $payout,
            "user" => $user,
        ]);
    }

    public function onWaiting(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $pictures = $this->getPictures($user["id"], 1);

        return $this->render($request, "home-user.twig", [
            "tab" => "waiting",
            "user" => $user,
            "stats" => $this->getUserStats($user),
            "pictures" => $pictures,
        ]);
    }

    public function onRejected(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $pictures = $this->getPictures($user["id"], 2);

        return $this->render($request, "home-user.twig", [
            "tab" => "rejected",
            "user" => $user,
            "stats" => $this->getUserStats($user),
            "pictures" => $pictures,
        ]);
    }

    protected function onManage(Request $request, Response $response, array $user)
    {
    }

    public function onManagePictures(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $news = $this->node->where("`published` = 1 AND `type` = 'picture' ORDER BY `updated` DESC");

        $news = array_map(function ($node) {
            if (!empty($node["file"]["width"])) {
                $w = $node["file"]["width"];
                $h = $node["file"]["height"];
                $r = $w / $h;

                $w = 100;
                $h = (int)round($w / $r);

                $node["file"]["twidth"] = $w;
                $node["file"]["theight"] = $h;
            }

            return $node;
        }, $news);

        return $this->render($request, "manage-news.twig", [
            "tab" => "pictures",
            "user" => $user,
            "news" => $news,
            "stats" => $this->getUserStats($user),
        ]);
    }

    public function onPayout(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        return $this->render($request, "payout.twig", [
            "user" => $user,
            "tab" => "payout",
        ]);
    }

    /**
     * Статистика продаж.
     **/
    public function onStats(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        return $this->render($request, "stats.twig", [
            "user" => $user,
            "tab" => "stats",
        ]);
    }

    /**
     * Список активных пользователей.
     **/
    public function onUsers(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $nodes = $this->node->where("`type` = 'user' AND `deleted` = 0 ORDER BY `published` DESC, `id`", [], function ($node) {
            return [
                "id" => $node["id"],
                "name" => $node["name"] ?? "(без имени)",
                "role" => $node["role"] ?? null,
                "email" => $node["email"],
                "published" => (int)$node["published"],
                "phone" => $node["phone"],
                "passport" => empty($node["passport"]) ? false : true,
            ];
        });

        return $this->render($request, "users.twig", [
            "tab" => "users",
            "user" => $user,
            "nodes" => $nodes,
            "stats" => $this->getUserStats($user),
        ]);
    }

    protected function getPictures($uid, $status)
    {
        $rows = $this->node->where("`type` = 'picture' AND `parent` = ? ORDER BY `created`", [$uid]);

        $rows = array_filter($rows, function ($node) use ($status) {
            return $node["status"] == $status;
        });

        $rows = array_map(function ($node) {
            if (!empty($node["file"]["width"])) {
                $w = $node["file"]["width"];
                $h = $node["file"]["height"];
                $r = $w / $h;

                $w = 100;
                $h = (int)round($w / $r);

                $node["file"]["twidth"] = $w;
                $node["file"]["theight"] = $h;
            }

            return $node;
        }, $rows);

        return $rows;
    }

    protected function getFile(array $files, array $user)
    {
        $name = null;
        $type = null;
        $body = null;

        $tmp = realpath($_SERVER["DOCUMENT_ROOT"] . "/../data/tmp");
        if (!$tmp or !is_writable($tmp)) {
            $this->logger->error("upload: folder {tmp} is not writable.", [
                "tmp" => $tmp,
            ]);
            throw new \RuntimeException("Не могу сохранить временный файл.");
        }

        if (empty($files["file"])) {
            $this->logger->error("upload: wrong file field name.");
            throw new \RuntimeException("Что-то не так с формой загрузки файла.");
        }

        if ($files["file"]->getError() != 0) {
            throw new \RuntimeException("Не удалось принять файл.");
        }

        $name = $files["file"]->getClientFilename();
        $type = $files["file"]->getClientMediaType();

        if (!in_array($type, ["image/png", "image/jpeg"])) {
            throw new \RuntimeException("Загрузить можно только PNG или JPEG.");
        }

        $tmp = tempnam($tmp, "upload_");
        $files["file"]->moveTo($tmp);
        $body = file_get_contents($tmp);
        unlink($tmp);

        if (!($img = imagecreatefromstring($body))) {
            throw new \RuntimeException("Не могу распознать изображение.");
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($w < 3000 or $h < 3000) {
            throw new \RuntimeException("Изображение должно быть не менее 3000 пикселей по меньшей стороне, размер вашего изображения: {$w}×{$h}.");
        }

        $file = $this->file->add($name, $type, $body, [
            "width" => $w,
            "height" => $h,
        ]);

        return $file["id"];
    }
}

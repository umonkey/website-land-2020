<?php
/**
 * Node UI.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;

class Node extends CommonHandler
{
    public function onEdit(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $node = $this->node->get($args["id"]);
        if (empty($node))
            $this->notfound();

        if (!$this->canEdit($user, $node))
            $this->forbidden();

        $text = json_encode($node, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $tab = "default";

        if ($node["type"] == "picture" and $node["status"] == 1)
            $tab = "waiting";

        if ($node["type"] == "picture") {
            if ($node["file"])
                $node["file"] = $this->node->get($node["file"]);
        }

        /*
        $categories = array_map(function ($c) {
            $name = $this->db->fetchcell("SELECT `name` FROM `categories` WHERE `id` = ?", [$c]);
            return [
                "id" => (int)$c,
                "name" => $name,
            ];
        }, (array)@$node["categories"]);
        */

        $categories = $this->getNodeCategories($node["categories"] ?? []);

        return $this->render($request, "node-edit.twig", [
            "tab" => $tab,
            "text" => $text,
            "node" => $node,
            "user" => $user,
            "stats" => $this->getUserStats($user),
            "categories" => $categories,
        ]);
    }

    public function onDelete(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $node = $this->node->get($args["id"]);
        if (empty($node))
            $this->notfound();

        if (!$this->canEdit($user, $node))
            $this->forbidden();

        $node['deleted'] = 1;
        $this->node->save($node);

        $next = $request->getParam('next');

        if ($node['type'] == 'picture') {
            return $response->withJSON([
                'messgae' => 'Картина удалена.',
            ]);
        }

        return $response->withJSON([
            'redirect' => $next ? $next : '/',
        ]);
    }

    public function onSudo(Request $request, Response $response, array $args)
    {
        $user = $this->requireAdmin($request);

        $node = $this->node->get($args["id"]);
        if (empty($node) or $node['type'] != 'user')
            $this->notfound();

        $this->sessionEdit($request, function ($data) use ($node) {
            $data['user_stack'][] = [
                'id' => $data['user_id'],
                'password' => $data['password'],
            ];

            $data['user_id'] = (int)$node['id'];
            $data['password'] = $node['password'];

            return $data;
        });

        $next = $request->getParam('next');

        return $response->withJSON([
            'redirect' => $next ? $next : '/',
        ]);
    }

    public function onSaveRaw(Request $request, Response $response, array $args)
    {
        try {
            $user = $this->requireUser($request);

            $node = $this->node->get($args["id"]);
            if (empty($node))
                $this->notfound();

            if ($node["parent"] != $user["id"])
                $this->forbidden("Это не ваш документ.");

            $this->db->beginTransaction();

            $text = $request->getParam("node");
            $news = json_decode($text, true);

            if ($news["id"] != $node["id"])
                $this->forbidden("Нельзя изменить id документа.");

            $this->node->save($news);

            $this->db->commit();

            return $response->withJSON([
                "message" => "Изменения сохранены.",
            ]);
        } catch (\Exception $e) {
            return $response->withJSON([
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display single node.
     **/
    public function onShow(Request $request, Response $response, array $args)
    {
        $node = $this->node->get($args["id"]);

        if (empty($node) or $node['deleted']) {
            $this->notfound();
        }

        if ($node['published'] == 0) {
            $this->forbidden();
        }

        if ($node['type'] == 'blog') {
            return $this->onShowBlog($request, $node);
        } elseif ($node['type'] == 'article') {
            return $this->onShowArticle($request, $node);
        }

        $template = "node-{$node["type"]}.twig";

        return $this->render($request, $template, [
            "node" => $node,
            'edit_link' => "/admin/nodes/{$node['id']}/edit",
        ]);
    }

    protected function onShowArticle(Request $request, array $node)
    {
        $node['type'] = 'wiki';  // FIXME: fake
        $page = $this->container->get('wiki')->renderPage($node);

        $jsdata = [];
        $jsdata = $this->addDisqus($jsdata, $node);

        $url = $node['url'] ?? "/node/{$node['id']}";

        return $this->render($request, 'node-article.twig', [
            'page' => $page,
            'edit_link' => "/admin/nodes/{$node['id']}/edit?back=" . urlencode($url),
            'breadcrumbs' => [
                ['link' => '/', 'label' => 'Главная'],
                ['link' => "/node/{$node['id']}.html", 'label' => 'Вы здесь'],
            ],
            'jsdata' => json_encode($jsdata),
        ]);
    }

    protected function onShowBlog(Request $request, array $node)
    {
        $node['type'] = 'wiki';  // FIXME: fake
        $page = $this->container->get('wiki')->renderPage($node);

        $jsdata = [];
        $jsdata = $this->addDisqus($jsdata, $node);

        $url = $node['url'] ?? "/node/{$node['id']}";

        return $this->render($request, 'node-blog.twig', [
            'page' => $page,
            'edit_link' => "/admin/nodes/{$node['id']}/edit?back=" . urlencode($url),
            'breadcrumbs' => [
                ['link' => '/', 'label' => 'Главная'],
                ['link' => '/blog/', 'label' => 'Блог'],
                ['link' => "/node/{$node['id']}.html", 'label' => 'Вы здесь'],
            ],
            'jsdata' => json_encode($jsdata),
        ]);
    }

    public function onShowCached(Request $request, Response $response, array $args)
    {
        return $this->onShow($request, $response, $args);
    }

    /**
     * Сохранение изменений в произвольном документе.
     *
     * Проводит валидацию доступа итп.
     **/
    public function onSave(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);
        if (!($role = $user["role"]))
            $role = "user";

        $form = $request->getParam("node");
        $redirect = $request->getParam('next');

        if (empty($form["id"])) {
            if (empty($form["type"])) {
                return $response->withJSON([
                    "message" => "Не указан тип объекта.",
                ]);
            }

            $node = $form;
            $node["published"] = 0;

            if ($node['type'] == 'picture')
                $node['status'] = 1;

            $node = $this->node->save($node);
        }

        else {
            $node = $this->node->get($form["id"]);
            unset($form["id"]);
            $node = array_merge($node, $form);
        }

        if (isset($node['kind']) and $node['kind'] == 'photo') {
            $node = $this->container->get('thumbnailer')->updateNode($node);
            $this->node->save($node);
        }

        $node = $this->node->save($node);

        // Отправляем обновление на боевой сайт, если это опубликованный товар.
        if ($node['type'] == 'picture') {
            $this->container->get('taskq')->add('publish-picture', [
                'id' => $node['id'],
            ]);
        }

        $next = $request->getParam("next");

        if (!empty($next)) {
            return $response->withJSON([
                "redirect" => $next,
            ]);
        } else {
            return $response->withJSON([
                "message" => "Изменения сохранены.",
            ]);
        }
    }

    public function onImportSales(Request $request, Response $response, array $args)
    {
        $url = SALES_JSON;
        $data = file_get_contents($url);
        $data = json_decode($data, true);

        $this->db->query("DELETE FROM sales");
        foreach ($data["items"] as $item) {
            $this->db->insert("sales", [
                "id" => $item["id"],
                "qty" => $item["qty"],
                "date" => $item["date"],
            ]);
        }

        return $response->withJSON([
            "message" => "OK",
        ]);
    }

    protected function canEdit(array $user, array $node)
    {
        if ($user["role"] == "admin")
            return true;

        if ($node["type"] == "user" and $node["id"] != $user["id"])
            return false;

        if ($user["id"] == $node["id"])
            return true;

        if ($node["author"] == $user["id"])
            return true;

        return false;
    }

    protected function canView(array $user, array $node)
    {
        if ($user['role'] == 'admin')
            return true;

        if ($node['type'] == 'user')
            return $node['id'] == $user['id'];

        // TODO: validate pictures

        return true;
    }

    public function getBreadcrumbs(Request $request, array $data)
    {
        $path = [];

        $path[] = [
            "label" => "Главная",
            "link" => "/",
        ];

        $path[] = [
            "label" => "Вы здесь",
            "link" => $request->getUri()->getPath(),
        ];

        return $path;
    }

    public function onComment(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $nid = $args["id"];
        $comment = $request->getParam("comment");
        $status = $request->getParam("status");

        if (!($node = $this->node->get($nid)))
            return $response->withJSON([
                "message" => "Нет такой работы.",
            ]);

        // TODO: acl

        $this->db->beginTransaction();

        if ($status)
            $node["status"] = (int)$status;

        $this->node->save($node);

        if (!empty($comment)) {
            $this->node->save([
                "type" => "comment",
                "parent" => $node["id"],
                "author" => $user["id"],
                "text" => $comment,
                "published" => 1,
            ]);
        }

        $this->taskq("new-comment", [
            "id" => $nid,
        ]);

        $this->taskq('publish-picture', [
            'id' => $nid,
        ]);

        $this->db->commit();

        if ($next = $request->getParam("next"))
            return $response->withJSON([
                "redirect" => $next,
            ]);
        else
            return $response->withJSON([
                "refresh" => true,
            ]);
    }

    /**
     * Генерация недостающих превьюшек.
     **/
    public function onUpdateThumbnail(Request $request, Response $response, array $args)
    {
        $this->db->beginTransaction();

        $node = $this->node->get($args["id"]);
        if (empty($node))
            $this->notfound();

        if ($node["type"] == "picture")
            $node = $this->node->get($node["file"]);

        if ($node["type"] != "file")
            $this->notfound();

        $tn = $this->container->get("thumbnailer");

        $node = $tn->updateNode($node);
        $this->node->save($node);

        $this->db->commit();

        debug($node['files']);
    }

    /**
     * Выгрузка в S3.
     **/
    public function onUploadS3(Request $request, Response $response, array $args)
    {
        $this->db->beginTransaction();

        $node = $this->node->get($args["id"]);
        if (empty($node))
            $this->notfound();

        if ($node["type"] == "picture")
            $node = $this->node->get($node["file"]);

        if ($node["type"] != "file")
            $this->notfound();

        $s3 = $this->container->get("S3");

        $node = $s3->uploadNodeFiles($node);

        $this->node->save($node);

        $this->db->commit();

        debug($node);
    }

    public function onDownload(Request $request, Response $response, array $args)
    {
        $node = $this->node->get($args["id"]);

        if (empty($node["files"][$args["code"]]))
            $this->notfound();

        $file = $node["files"][$args["code"]];

        if ($file["storage"] == "local") {
            $body = $this->file->fsget($file['path']);
            if (empty($body)) {
                $this->logger->warning('node {0} has no {1} version.', [$node['id'], $args['code']]);
                $this->notfound("file {$file['path']} not found");
            }

            $type = $file["type"];
            $length = strlen($body);
            $hash = md5($body);

            $response = $response->withHeader("Content-Type", $type)
                ->withHeader("Content-Length", $length)
                ->withHeader("ETag", "\"{$hash}\"")
                ->withHeader("Cache-Control", "public, max-age=31536000");
            $response->getBody()->write($body);

            return $response;
        }

        elseif ($file["storage"] == "s3") {
            return $response->withRedirect($file["url"]);
        }

        else {
            $this->notfound();
        }
    }

    /**
     * Скачивание исходного файла.
     **/
    public function onDownloadFile(Request $request, Response $response, array $args)
    {
        $node = $this->node->get($args["id"]);

        if (empty($node["files"]['original']))
            $this->notfound();

        if ($request->getMethod() == 'GET') {
            $info = [
                'name' => $node['name'],
                'length' => $node['files']['original']['length'],
                'type' => $file['type'],
            ];

            return $this->render($request, 'download.twig', [
                'info' => $info,
            ]);
        }

        $password = $request->getParam('password');
        if ($password != '9261')
            return $response->withRedirect($request->getUri()->getPath() . '?message=wrong');

        $file = $node['files']['original'];
        if ($file['storage'] == 's3') {
            $body = @file_get_contents($file['url']);
        } else {
            $body = $this->file->fsget($file['path']);
        }

        if (empty($body))
            $this->unavailable();

        $name = urlencode($node['name']);

        $response = $response->withHeader("Content-Type", $node['mime_type'])
            ->withHeader("Content-Length", strlen($body))
            ->withHeader("Content-Disposition", "attachment; filename=\"{$node['name']}\"");
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Генератор картинки для шаринга страницы.
     *
     * Использует шаблон из файла public/images/kdpv-template.png
     **/
    public function onKDPV(Request $request, Response $response, array $args)
    {
        $nid = $args['id'];

        $node = $this->node->get($nid);
        if (empty($node)) {
            $this->notfound();
        }

        $text = $node['kdpv_text'] ?? $node['title'] ?? $node['name'] ?? $node['subtitle'];
        if (empty($text)) {
            $this->notfound();
        }

        $template = $_SERVER['DOCUMENT_ROOT'] . '/images/kdpv-template.png';

        $kdpv = new \App\NodePicture();
        $image = $kdpv->render($template, $text);

        $response->getBody()->write($image);
        return $response->withHeader('Content-Type', 'image/png');
    }

    /**
     * Возвращает список категорий по идентификаторам.
     *
     * @param array $ids Идентификаторы категорий.
     * @return array Описания категорий, с ключами id и name.
     **/
    protected function getNodeCategories(array $ids)
    {
        $categories = [];

        foreach ($ids as $id) {
            $item = [
                'id' => (int)$id,
                'path' => [],
            ];

            while ($id) {
                $tmp = $this->db->fetchone('SELECT * FROM `categories` WHERE `id` = ?', [$id]);
                array_unshift($item['path'], $tmp['name']);
                $id = (int)$tmp['parent'];
            }

            $item['name'] = implode(' » ', $item['path']);

            $categories[] = $item;
        }

        usort($categories, function ($a, $b) {
            return strcmp(mb_strtolower($a['name']), mb_strtolower($b['name']));
        });

        return $categories;
    }

    protected function addDisqus(array $jsdata, array $node)
    {
        $path = substr($node['url'] ?? "/node/{$node['id']}", 1);

        $disqus_url = 'https://land.umonkey.net/' . $path;
        $disqus_id = $path;

        if (substr($disqus_id, -1) == '/') {
            $disqus_id .= 'index.html';
        }

        $jsdata['disqus_url'] = $disqus_url;
        $jsdata['disqus_id'] = $disqus_id;
        $jsdata['disqus_title'] = $node['name'];

        return $jsdata;
    }
}

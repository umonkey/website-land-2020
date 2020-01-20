<?php
/**
 * @see vendor/umonkey/ufw1/src/Ufw1/CommonHandler.php
 **/

namespace App;

use Slim\Http\Request;
use Slim\Http\Response;

class CommonHandler extends \Ufw1\CommonHandler
{
    protected function requireAdmin(Request $request)
    {
        $user = $this->requireUser($request);

        if ($user["role"] != "admin")
            $this->forbidden();

        return $user;
    }

    /**
     * Makes sure the user is logged in.
     *
     * Reads user id from the session, loads it and returns the info.
     * On any error throws Unhandler or Forbidden.
     *
     * @param Request $request Request info.
     * @return array User info.
     **/
    protected function requireUser(Request $request)
    {
        $session = $this->sessionGet($request);
        if (empty($session) or empty($session["user_id"]))
            $this->unauthorized($request);

        $user = $this->node->get($session["user_id"]);
        if (empty($user))
            $this->unauthorized($request);

        $user = array_merge([
            "published" => 0,
            "role" => "user",
        ], $user);

        if ($user["published"] == 0) {
            error_log("auth: user {$user["id"]} not published");
            $this->forbidden($request);
        }

        return $user;
    }

    protected function isAdmin(Request $request)
    {
        $session = $this->sessionGet($request);

        if (empty($session))
            return false;

        if (empty($session["user_id"]))
            return false;

        return true;
    }

    /**
     * Renders the page using a template.
     *
     * @param Request $request Request info, used to get host, path information, etc.
     * @param string $templateName File name, e.g. "pages.twig".
     * @param array $data Template variables.
     * @return Response ready to use response.
     **/
    protected function renderHTML(Request $request, $templateName, array $data = [])
    {
        $defaults = [
            "request" => [
                "host" => $request->getUri()->getHost(),
                "path" => $request->getUri()->getPath(),
                "get" => $request->getQueryParams(),
                "uri" => $_SERVER["REQUEST_URI"],
            ],
        ];

        $data = array_merge($defaults, $data);

        $html = $this->template->render($templateName, $data);
        return $html;
    }

    /**
     * Render markdown code to html.
     *
     * Handles wiki links and stuff.
     *
     * @param string $source Source code.
     * @return string Resulting HTML.
     **/
    protected function renderMarkdown($source)
    {
        // Process wiki links.
        $source = preg_replace_callback('@\[\[([^]]+)\]\]@', function ($m) {
            $link = $m[1];
            $label = $m[1];

            if (count($parts = explode("|", $m[1], 2)) == 2) {
                $link = $parts[0];
                $label = $parts[1];
            }

            $cls = "good";
            if (empty($this->db->fetch("SELECT `name` FROM `pages` WHERE `name` = ?", [$link])))
                $cls = "broken";

            $html = sprintf("<a href='/wiki?name=%s' class='wiki %s'>%s</a>", urlencode($link), $cls, htmlspecialchars($label));

            // TODO: embed files

            return $html;
        }, $source);

        $html = \App\Common::renderMarkdown($source);
        $html = \App\Common::renderTOC($html);

        $html = \App\Util::cleanHtml($html);

        return $html;
    }

    protected function search($query)
    {
        return array_map(function ($em) {
            $name = substr($em["key"], 5);
            $link = "/wiki?name=" . urlencode($name);

            return [
                "link" => $link,
                "title" => $name,
                "snippet" => @$em["meta"]["snippet"],
                "updated" => @$em["meta"]["updated"],
                "image" => @$em["meta"]["image"],
            ];
        }, $this->fts->search($query));
    }

    protected function cacheGet($key)
    {
        return $this->db->fetchcell("SELECT `value` FROM `cache` WHERE `key` = ?", [$key]);
    }

    protected function cacheSet($key, $value)
    {
        $this->db->query("DELETE FROM `cache` WHERE `key` = ?", [$key]);

        $this->db->insert("cache", [
            "key" => $key,
            "added" => time(),
            "value" => $value,
        ]);
    }

    protected function nodeGet($id)
    {
        $tmp = $this->db->fetchOne("SELECT * FROM `nodes` WHERE `id` = ?", [$id]);
        if ($tmp)
            return $this->unpack($tmp);
    }

    protected function unpack(array $row)
    {
        if (array_key_exists("more", $row)) {
            if ($row["more"][0] == '{')
                $more = json_decode($row["more"], true);
            else
                $more = unserialize($row["more"]);
            unset($row["more"]);
            if (is_array($more))
                $row = array_merge($row, $more);
        }

        return $row;
    }

    protected function packMovie(array $row)
    {
        return $this->pack($row, [
            "id",
            "created",
            "updated",
            "deleted",
            "title",
            "text",
            "duration",
            "kinopoisk",
            "start",
            "end",
        ]);
    }

    protected function packNode(array $row)
    {
        return $this->pack($row, [
            "id",
            "parent",
            "lb",
            "rb",
            "type",
            "created",
            "updated",
            "key",
            "published",
        ]);
    }

    protected function pack(array $row, array $fields)
    {
        $more = [];

        foreach ($row as $k => $v) {
            if ($v === "")
                $v = null;

            if (!in_array($k, $fields)) {
                $more[$k] = $v;
                unset($row[$k]);
            }
        }

        $row["more"] = $more ? serialize($more) : null;

        return $row;
    }

    protected function nodeSave(array $node)
    {
        $node = $this->packNode($node);

        if (empty($node["id"]))
            return $this->db->insert("nodes", $node);
        else {
            $id = $node["id"];
            unset($node["id"]);

            return $this->db->update("nodes", $node, [
                "id" => $id,
            ]);
        }
    }

    protected function getUserStats(array $user)
    {
        if (@$user["role"] == "admin")
            return $this->getManagerStats($user);

        $res = [
            "accepted" => 0,
            "rejected" => 0,
            "waiting" => 0,
            "other" => 0,
        ];

        $nodes = $this->node->where("`type` = 'picture' AND `parent` = ?", [$user["id"]]);
        foreach ($nodes as $node) {
            if ($node["status"] == 0)
                $res["waiting"]++;
            elseif ($node["status"] == 1)
                $res["waiting"]++;
            elseif ($node["status"] == 2)
                $res["rejected"]++;
            else
                $res["other"]++;
        }

        return $res;
    }

    protected function getManagerStats(array $user)
    {
        $res = [
            "news" => 0,
            "users" => 0,
            "pictures" => 0,
        ];

        $val = $this->db->fetchcell("SELECT COUNT(1) FROM `nodes` WHERE `type` IN ('user', 'picture') AND `published` = 0");
        $res["news"] = (int)$val;

        $val = $this->db->fetchcell("SELECT COUNT(1) FROM `nodes` WHERE `type` = 'user' AND `published` = 1");
        $res["users"] = (int)$val;

        $val = $this->db->fetchcell("SELECT COUNT(1) FROM `nodes` WHERE `type` = 'picture' AND `published` = 1");
        $res["pictures"] = (int)$val;

        return $res;
    }

    protected function getLogger()
    {
        return $this->container->get("logger");
    }

    protected function fillNodes(array $nodes)
    {
        $nodes = array_map(function ($node) {
            if (!empty($node["file"])) {
                $node["file"] = $this->node->get($node["file"]);
                $node["file"] = $this->fixFile($node["file"]);
            }

            if (!empty($node["author"])) {
                $node["author"] = $this->node->get($node["author"]);
                unset($node["author"]["password"]);
            }

            return $node;
        }, $nodes);

        return $nodes;
    }

    /**
     * Дополнение информации о файле.
     *
     * Добавляет в массив ссылки.
     *
     * TODO: вынести в Twig.
     **/
    protected function fixFile(array $node)
    {
        return $node;
    }

    /**
     * Подгружает файлы в массив нод.
     *
     * Сканирует рекурсивно массив, если находит ключ file с числовым значением -- загужает и вставляет ноду.
     *
     * @param array $nodes Описание нод или одной ноды.
     * @return array Модифицированный массив.
     **/
    protected function loadFiles(array $nodes)
    {
        foreach ($nodes as $k => $v) {
            if ($k == "file" and is_numeric($v))
                $nodes[$k] = $this->node->get($v);
            elseif (is_array($v))
                $nodes[$k] = $this->loadFiles($v);
        }

        return $nodes;
    }
}

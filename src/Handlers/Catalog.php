<?php
/**
 * Подтягивание каталога извне.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;

class Catalog extends CommonHandler
{
    public function onPull(Request $request, Response $response, array $args)
    {
        $url = "https://www.artwall.ru/catalog.json";

        if ($res = file_get_contents($url)) {
            $res = json_decode($res, true);

            $this->db->beginTransaction();
            $this->db->query("DELETE FROM `categories`");

            foreach ($res["categories"] as $row)
                $this->db->insert("categories", $row);

            $this->db->commit();

            $text = "OK";
        } else {
            $text = "ERROR";
        }

        $response = $response->withHeader("Content-Type", "text/plain; charset=utf-8");
        $response->getBody()->write($text);

        return $response;
    }

    public function onSearch(Request $request, Response $response, array $args)
    {
        $query = $request->getParam("query");
        if (mb_strlen($query) < 3)
            return $response->withJSON([
                "results" => [],
            ]);

        $pattern = '%' . $query . '%';
        $rows = $this->db->fetch("SELECT * FROM `categories` WHERE `name` LIKE ? AND `id` NOT IN (SELECT `parent` FROM `categories` WHERE `parent` IS NOT NULL)", [$pattern]);

        $rows = array_map(function ($row) {
            $tmp = $this->getPath($row["id"]);
            if ($tmp === null)
                return null;
            return [
                (int)$row["id"],
                $tmp,
            ];
        }, $rows);

        $rows = array_filter($rows);

        return $response->withJSON([
            "results" => $rows,
        ]);
    }

    protected function getPath($id)
    {
        $items = [];

        while ($id) {
            $row = $this->db->fetchone("SELECT parent, name FROM categories WHERE id = ?", [$id]);
            if (empty($row))
                return null;
            $items[] = $row["name"];
            $id = $row["parent"];
        }

        $items = array_reverse($items);
        return $items;
    }
}

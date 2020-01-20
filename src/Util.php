<?php

namespace App;

class Util extends \Ufw1\Util
{
    public static function fetch($url)
    {
        $context = stream_context_create(array(
            "http" => array(
                "method" => "GET",
                "ignore_errors" => true,
                ),
            ));

        $res = array(
            "status" => null,
            "status_text" => null,
            "headers" => array(),
            "data" => @file_get_contents($url, false, $context),
            );

        if (!empty($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('@^HTTP/[0-9.]+ (\d+) (.*)$@', $h, $m)) {
                    $res["status"] = $m[1];
                    $res["status_text"] = $m[2];
                } else {
                    $parts = explode(":", $h, 2);
                    $k = strtolower(trim($parts[0]));
                    $v = trim($parts[1]);
                    $res["headers"][$k] = $v;
                }
            }
        }

        if (false === $res["data"])
            $res["error"] = error_get_last();

        return $res;
    }

    /**
     * Разбор описания страницы.
     *
     * Вытаскивает метаданные и свойства.
     *
     * @param array $page Запись из таблицы pages.
     * @return array Описание страницы.
     **/
    public static function parsePage(array $page)
    {
        $props = [
            "name" => $page["name"],
            "title" => $page["name"],
            "language" => "ru",
        ];

        $text = $page["source"];
        $lines = preg_split('@(\r\n|\n)@', $text);
        foreach ($lines as $idx => $line) {
            if (preg_match('@^([a-z0-9_]+):\s+(.+)$@', $line, $m)) {
                $props[$m[1]] = $m[2];
            } elseif ($line == "---") {
                $lines = array_slice($lines, $idx + 1);
                $text = implode("\r\n", $lines);
                break;
            }
        }

        $props["text"] = $text;
        return $props;
    }

    public static function parseHtmlAttrs($tag)
    {
        $res = [];

        if (preg_match_all('@([a-z-]+)="([^"]+)"@', $tag, $m)) {
            foreach ($m[1] as $idx => $key)
                $res[$key] = trim($m[2][$idx]);
        }

        if (preg_match_all("@([a-z-]+)='([^']+)'@", $tag, $m)) {
            foreach ($m[1] as $idx => $key)
                $res[$key] = trim($m[2][$idx]);
        }

        return $res;
    }

    public static function installErrorHandler($container)
    {
        $container['errorHandler'] = function ($c) {
            return function ($request, $response, $e) use ($c) {
                $h = new Controllers\ErrorController($c);
                return $h($request, $response, ['exception' => $e]);
            };
        };
    }
}

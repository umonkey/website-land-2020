#!/usr/bin/env php
<?php

/**
 * Редактирование таблицы редиректов
 **/

$app = require __DIR__ . '/../config/bootstrap.php';

$container = $app->getContainer();
$db = $container->get('db');

if (count($argv) == 2) {
    $db->beginTransaction();
    $db->query('DELETE FROM rewrite');

    $f = fopen($argv[1], 'r');
    while (false !== ($line = fgets($f, 2048))) {
        list($src, $dst) = explode(",", trim($line), 2);
        $db->insert('rewrite', [
            'src' => $src,
            'dst' => $dst,
        ]);
    }
    fclose($f);

    $db->commit();
}

else {
    $map = [];

    $rows = $db->fetch('SELECT src, dst FROM rewrite WHERE dst IS NOT NULL ORDER BY src');
    foreach ($rows as $row) {
        $map[$row['src']] = $row['dst'];
    }

    $nodes = $container->node->where('type = ? AND deleted = 0', ['wiki']);
    foreach ($nodes as $node) {
        if (!empty($node['url'])) {
            $map[$node['url']] = "/wiki?name=" . urlencode($node['name']);;
        }
    }

    ksort($map);

    foreach ($map as $src => $dst) {
        printf("%s,%s\n", $src, $dst);
    }
}

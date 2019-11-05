<?php
/**
 * Run utility tasks.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;


class Cron extends CommonHandler
{
    /**
     * Конвертация файлов.
     **/
    public function onDefault(Request $request, Response $response)
    {
        $nodes = $this->node->where("type = 'file'");
        foreach ($nodes as $node) {
            if (empty($node["files"])) {
                $node["files"] = [];

                $node["files"]["original"] = [
                    "type" => $node["mime_type"],
                    "length" => $node["length"],
                    "storage" => "local",
                    "path" => $node["fname"],
                    "width" => $node["width"],
                    "height" => $node["height"],
                ];

                $this->node->save($node);
            }
        }

        debug("Done.");
    }

    /**
     * Выгрузка файлов в S3.
     **/
    public function onPushS3(Request $request, Response $response)
    {
        set_time_limit(0);

        $settings = $this->container->get("settings");
        if (empty($settings["S3"]))
            $this->unavailable("S3 not configured.");

        $s3 = $this->container->get("S3");

        $this->db->beginTransaction();

        $nodes = $this->node->where("type = 'file'");

        foreach ($nodes as $node) {
            $node = $this->addThumbnails($node);

            if (!empty($node["files"])) {
                $saveChanges = false;

                foreach ($node["files"] as $idx => &$file) {
                    if ($file["storage"] == "local") {
                        $folder = realpath($settings["files"]["path"]);
                        $path = $folder . "/" . $file["path"];

                        if (!is_readable($path)) {
                            $this->logger->warning("s3: file {path} is not readable.", [
                                "path" => $path,
                            ]);
                        } else {
                            $body = file_get_contents($path);

                            $this->logger->debug("s3: uploading {len} bytes to {path}", [
                                "len" => strlen($body),
                                "path" => $file["path"],
                            ]);

                            $res = $s3->putObjectBody("/" . $file["path"], $body, [
                                "type" => $file["type"],
                                "acl" => "public-read",
                            ]);

                            if ($res[0]["status"] == 200) {
                                $file["storage"] = "s3";
                                $saveChanges = true;

                                $this->logger->info("s3: node {id}: file {path} uploaded to S3.", [
                                    "id" => $node["id"],
                                    "path" => $file["path"],
                                ]);
                            }
                        }
                    }
                }

                if ($saveChanges) {
                    $this->node->save($node);

                    $this->logger->debug("s3: files uploaded, new node contents: {node}", [
                        "node" => $node,
                    ]);
                }
            }
        }

        $this->db->commit();

        debug("Done.");
    }

    protected function addThumbnails(array $node)
    {
        if ($node["type"] != "file")
            return $node;

        $tn = $this->container->get("thumbnailer");
        $node = $tn->updateNode($node);

        return $node;
    }
}

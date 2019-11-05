<?php
/**
 * Handle file uploads.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;

class Upload extends CommonHandler
{
    /**
     * Приём файла от пользователя.
     *
     * Сохраняет файл на диске, описывает его в базе,
     * готовит миниатюры, затем перекидывает на новый документ.
     **/
    public function onUpload(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        $this->db->beginTransaction();

        $files = $request->getUploadedFiles();
        if (empty($files["file"]))
            $this->fail("Не удалось принять файл, попробуйте снова.");

        $name = $files["file"]->getClientFilename();
        $type = $files["file"]->getClientMediaType();

        if (!in_array($type, ["image/png", "image/jpeg"]))
            $this->fail("Загрузить можно только PNG или JPEG.");

        list($body, $w, $h) = $this->getFileBody($files['file']);

        if ($w < 3000 or $h < 3000)
            $this->fail("Изображение должно быть не менее 3000 пикселей по меньшей стороне, размер вашего изображения: {$w}×{$h}.");

        $node = $this->file->add($name, $type, $body, [
            "width" => $w,
            "height" => $h,
            "owner" => $user["id"],
        ]);

        $this->logger->info("user {uid} uploaded file {fid}", [
            "uid" => $user["id"],
            "fid" => $node["id"],
        ]);

        $this->taskq('update-node-thumbnail', [
            'id' => $node['id'],
        ]);

        $this->taskq('telega', [
            'message' => "Загружен новый файл: {$node['name']}\nhttps://artwall.pro/admin/nodes/{$node['id']}/edit",
        ]);

        $this->db->commit();

        return $response->withJSON([
            "redirect" => "/submit?file={$node["id"]}",
        ]);
    }

    protected function getFileBody($file)
    {
        $tmp_dir = realpath($_SERVER["DOCUMENT_ROOT"] . "/../tmp");
        if (!is_dir($tmp_dir) or !is_writable($tmp_dir)) {
            $this->logger->error("upload: temporary dir not writable: {dir}", [
                "dir" => $tmp_dir,
            ]);

            $this->fail("Не удалось принять файл.");
        }

        $tmp = tempnam($tmp_dir, "upload_");
        $file->moveTo($tmp);
        $body = file_get_contents($tmp);

        $res = getimagesize($tmp);
        if ($res === false)
            $this->fail("Не удалось распознать изображение.");
        else {
            $w = $res[0];
            $h = $res[1];
        }

        unlink($tmp);

        return [$body, $w, $h];
    }
}

<?php
/**
 * Handle submissions.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use App\CommonHandler;

class Submit extends CommonHandler
{
    /**
     * Вывод формы добавления файла.
     **/
    public function onSubmit(Request $request, Response $response)
    {
        $user = $this->requireUser($request);

        $fid = $request->getParam("file");
        $file = $this->file->get($fid);

        if ($file["kind"] != "photo") {
            $this->logger->error("submit: file {id} is not an image, kind={kind}", [
                "id" => $fid,
                "kind" => $file["kind"],
            ]);

            $this->notfound();
        }

        return $this->render($request, "submit.twig", [
            "file" => $file,
            "user" => $user,
        ]);
    }
}

<?php
/**
 * Account operations.
 *
 * Lets users log in.
 **/

namespace App\Handlers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\CommonHandler;


class Account extends \Ufw1\Handlers\Account
{
    public function onGetLoginForm(Request $request, Response $response, array $args)
    {
        $back = @$_GET["back"];

        return $this->render($request, "login.twig", [
            "title" => "Идентификация",
            "back" => $back,
        ]);
    }

    public function onRegister(Request $request, Response $response, array $args)
    {
        if ($request->getMethod() == "GET") {
            if ($user = $this->getUser($request)) {
                return $this->render($request, "registered-already.twig");
            }
        }

        return parent::onRegister($request, $response, $args);
    }

    public function onLogout(Request $request, Response $response, array $args)
    {
        $this->sessionEdit($request, function ($data) {
            if (!empty($data['user_stack'])) {
                $em = array_pop($data['user_stack']);
                if (empty($data['user_stack']))
                    unset($data['user_stack']);

                $data['user_id'] = $em['id'];
                $data['password'] = $em['password'];
            } else {
                unset($data['user_id']);
                unset($data['password']);
            }

            return $data;
        });

        return $response->withRedirect("/");
    }

    public function onProfile(Request $request, Response $response, array $args)
    {
        $user = $this->requireUser($request);

        return $this->render($request, "profile.twig", [
            "tab" => "profile",
            "node" => $user,
            "user" => $user,
        ]);
    }

    public function getBreadcrumbs(Request $request, array $data)
    {
        $path = [];

        $path[] = [
            "label" => "Главная",
            "link" => "/",
        ];

        $path[] = [
            "label" => "Настройки",
            "link" => $request->getUri()->getPath(),
        ];

        return $path;
    }

    protected function checkRegisterForm(array &$form)
    {
        if (empty($form["phone"]))
            $this->fail("Не указан номер телефона.");

        $digits = preg_replace('@[^0-9]+@', '', $form["phone"]);

        if ($digits[0] == "9")
            $digits = "7" . $digits;
        elseif ($digits[0] == "8")
            $digits = "7" . substr($digits, 1);

        if (strlen($digits) != 11)
            $this->fail("Введите номер мобильного телефона.");

        $form["phone"] = "+" . $digits;

        return parent::checkRegisterForm($form);
    }

    public function onEnableUser(Request $request, Response $response, array $args)
    {
        $user = $this->requireAdmin($request);

        $id = $request->getParam("id");
        $enabled = $request->getParam("enabled");

        if ($id != $user["id"]) {
            $node = $this->node->get($id);
            $node["published"] = (int)$enabled;
            $this->node->save($node);
        }

        return $response->withJSON([
            "message" => "ОК",
        ]);
    }
}

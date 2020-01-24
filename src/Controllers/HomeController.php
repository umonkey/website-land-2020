<?php

/**
 * Главная страница.
 *
 * Выводит готовый шаблон, никаких данных.
 **/

declare(strict_types=1);

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use App\Controller;

class HomeController extends Controller
{
    public function index(Request $request, Response $response, array $args): Response
    {
        return $this->render($request, 'pages/home.twig', [
        ]);
    }
}

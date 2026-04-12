<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Views\Twig;
use Lineup\Src\Application\Actions\AuthActions;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    });

    $app->get('/login', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig');
    });
    
    $app->get('/register', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'register.twig');
    });

    $app->post('/login', [AuthActions::class, 'login']);
    $app->post('/register', [AuthActions::class, 'register']);
    $app->get('/logout', [AuthActions::class, 'logout']);


    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};

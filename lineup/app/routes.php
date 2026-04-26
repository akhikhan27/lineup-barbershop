<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Views\Twig;
use App\Application\Actions\AuthActions;
use App\Application\Actions\AppointmentActions;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

return function (App $app) {
    $adminMiddleware = function (Request $request, RequestHandler $handler){
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        $response = new Slim\Psr7\Response();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    return $handler->handle($request);
    };

    $userMiddleware = function (Request $request, RequestHandler $handler) {
        if (!isset($_SESSION['user'])){
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location','/login')->withStatus(302);
        }
        return $handler->handle($request);
    };

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig');
    });

    $app->get('/services', function (Request $request, Response $response) {
        $stmt = $this->get(PDO::class)->prepare('SELECT * FROM services');
        $stmt->execute();
        $services = $stmt->fetchAll();
        $view = Twig::fromRequest($request);
        return $view->render($response,'services.twig',['services' => $services]);
    });

    $app->get('/book', [AppointmentActions::class, 'showBookingForm'])->add($userMiddleware);
    $app->post('/book', [AppointmentActions::class, 'bookAppointment'])->add($userMiddleware);

    $app->get('/login', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig');
    });
    
    $app->get('/register', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'register.twig');
    });

    $app->get('/admin/register', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, '/admin/register.twig');
    });

    $app->post('/admin/register', [AuthActions::class, 'register']);
    $app->post('/login', [AuthActions::class, 'login']);
    $app->post('/register', [AuthActions::class, 'register']);
    $app->get('/logout', [AuthActions::class, 'logout']);


    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->group('/admin', function (Group $group) {
    //$group->get('/services', [AdminController::class, 'dashboard']);
    //other admin routes
    })->add($adminMiddleware);

    
};

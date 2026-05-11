<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use App\Application\Middleware\TranslationMiddleware;
use Slim\App;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(TranslationMiddleware::class);
    $app->add(TwigMiddleware::create($app, $app->getContainer()->get('view')));
};

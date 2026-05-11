<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

class TranslationMiddleware implements Middleware
{
    private Translator $translator;
    private Twig $twig;

    public function __construct(Translator $translator, Twig $twig)
    {
        $this->translator = $translator;
        $this->twig = $twig;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $lang = $request->getQueryParams()['lang'] ?? $_SESSION['lang'] ?? $_COOKIE['lang'] ?? 'en';
        $lang = in_array($lang, ['en', 'fr']) ? $lang : 'en';
        $_SESSION['lang'] = $lang;
        if ($request->getQueryParams()['lang'] ?? null) {
            setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');
        }
        $this->translator->setLocale($lang);
        $this->twig->getEnvironment()->addGlobal('current_locale', $lang);
        $this->twig->getEnvironment()->addGlobal('session', $_SESSION);
        $response = $handler->handle($request);
        return $response;
    }
}

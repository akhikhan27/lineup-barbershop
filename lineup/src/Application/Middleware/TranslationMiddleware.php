<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Translation\Translator;

class TranslationMiddleware implements Middleware
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $lang = $request->getQueryParams()['lang'] ?? $_SESSION['lang'] ?? 'en';
        $lang = in_array($lang, ['en', 'fr']) ? $lang : 'en';
        $_SESSION['lang'] = $lang;
        $this->translator->setLocale($lang);
        return $handler->handle($request);
    }
}

<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Twig\TranslationExtension;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $dbSettings = $settings->get('db');
            $dsn = "mysql:host={$dbSettings['host']};dbname={$dbSettings['dbname']};charset=utf8";
            return new PDO($dsn, $dbSettings['user'], $dbSettings['password']);
        },
        Translator::class => function () {
            $translator = new Translator('en');
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', __DIR__ . '/../translations/messages.en.yaml', 'en');
            $translator->addResource('yaml', __DIR__ . '/../translations/messages.fr.yaml', 'fr');
            return $translator;
        },
        'view' => function (ContainerInterface $c) {
            $twig = \Slim\Views\Twig::create(__DIR__ . '/../src/Views', ['cache' => false]);
            $twig->getEnvironment()->addGlobal('session', $_SESSION ?? []);
            $twig->addExtension($c->get(TranslationExtension::class));
            return $twig;
        },
        \Slim\Views\Twig::class => function (ContainerInterface $c) {
            return $c->get('view');
        },
    ]);
};

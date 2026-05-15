<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => false, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
               'db' => [
                'host'     => getenv('DB_HOST') ?: 'shortline.proxy.rlwy.net',
                'dbname'   => getenv('DB_NAME') ?: 'railway',
                'user'     => getenv('DB_USER') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: 'EeOSLGovWMhnwSryXhohptXyRYaZWImU',
                'port'     => getenv('DB_PORT') ?: '37105',
                ],
            ]);
        }
    ]);     
};

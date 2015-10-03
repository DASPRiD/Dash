<?php
return [
    'service_manager' => [
        'factories' => [
            Dash\Router::class => Dash\RouterFactory::class,
            Dash\Parser\ParserManager::class => Dash\Parser\ParserManagerFactory::class,
            Dash\Route\RouteManager::class => Dash\Route\RouteManagerFactory::class,
        ],
    ],
];

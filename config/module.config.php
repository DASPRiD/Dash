<?php
return [
    'service_manager' => [
        'invokables' => [
            Dash\Router\Parser\ParserManager::class => Dash\Router\Parser\ParserManager::class,
            Dash\Router\Route\RouteManager::class => Dash\Router\Route\RouteManager::class,
        ],
        'factories' => [
            Dash\Router\Router::class => Dash\Router\Http\RouterFactory::class,
        ],
    ],
];

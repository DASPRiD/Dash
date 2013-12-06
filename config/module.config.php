<?php
return [
    'service_manager' => [
        'invokables' => [
            'Dash\Router\Http\Parser\ParserManager' => 'Dash\Router\Http\Parser\ParserManager',
            'Dash\Router\Http\Route\RouteManager' => 'Dash\Router\Http\Route\RouteManager',
        ],
        'factories' => [
            'Dash\Router\Http\Router' => 'Dash\Router\Http\RouterFactory',
        ],
    ],
];

<?php
return [
    'service_manager' => [
        'invokables' => [
            'Dash\Mvc\Router\Http\Parser\ParserManager' => 'Dash\Mvc\Router\Http\Parser\ParserManager',
            'Dash\Mvc\Router\Http\Route\RouteManager' => 'Dash\Mvc\Router\Http\Route\RouteManager',
        ],
        'factories' => [
            'Dash\Mvc\Router\Http\Router' => 'Dash\Mvc\Router\Http\RouterFactory',
        ],
    ],
];

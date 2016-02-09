<?php
return [
    'dependencies' => [
        'factories' => [
            \Dash\Parser\ParserManager::class => \Dash\Parser\ParserManagerFactory::class,
            \Dash\Route\RouteManager::class => \Dash\Route\RouteManagerFactory::class,
            \Dash\Router::class => \Dash\RouterFactory::class,
            'DashBaseUri' => \Dash\BaseUriFactory::class,
            'DashRootRouteCollection' => \Dash\RootRouteCollectionFactory::class,
        ],
    ],
];

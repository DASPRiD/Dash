<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http;

use Dash\Mvc\Router\Http\RouteCollection\RouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the HTTP router.
 */
class RouterFactory implements FactoryInterface
{
    /**
     * @return Router
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $router = new Router(new RouteCollection($serviceLocator->get('Dash\Mvc\Router\Http\Route\RouteManager')));
        $config = $serviceLocator->get('config');

        if (isset($config['dash_router']['base_url'])) {
            $router->setBaseUrl($config['dash_router']['base_url']);
        }

        if (isset($config['dash_router']['routes']) && is_array($config['dash_router']['routes'])) {
            $routeCollection = $router->getRouteCollection();

            foreach ($config['dash_router']['routes'] as $name => $route) {
                $routeCollection->insert($name, $route, isset($route['priority']) ? $route['priority'] : 1);
            }
        }

        return $router;
    }
}
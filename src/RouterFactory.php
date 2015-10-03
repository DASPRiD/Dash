<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash;

use Dash\Route\RouteManager;
use Dash\RouteCollection\RouteCollection;
use GuzzleHttp\Psr7\Uri;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for the default router.
 */
class RouterFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  array              $options
     * @return Router
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $routeCollection = new RouteCollection($container->get(RouteManager::class));
        $config          = $container->has('config') ? $container->get('config') : [];

        if (isset($config['dash']['routes']) && is_array($config['dash']['routes'])) {
            foreach ($config['dash']['routes'] as $name => $route) {
                $routeCollection->insert($name, $route, isset($route['priority']) ? $route['priority'] : 1);
            }
        }

        if (isset($config['dash']['base_uri'])) {
            $baseUri = new Uri($config['dash']['base_uri']);
        } elseif ($container->has('Request') && method_exists($request = $container->getRequest(), 'getBasePath')) {
            $baseUri = (new Uri($request->getUriString()))->withPath($request->getBasePath());
        } else {
            throw new Exception\RuntimeException('Could not determine a base URI');
        }

        return new Router($routeCollection, $baseUri);
    }
}

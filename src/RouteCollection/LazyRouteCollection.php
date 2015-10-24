<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\RouteCollection;

use Dash\Exception;
use IteratorAggregate;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Lazy route collection which only instantiates routes when required.
 */
class LazyRouteCollection implements IteratorAggregate, RouteCollectionInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $routeManager;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * @param ServiceLocatorInterface $routeManager
     * @param array[]                 $routes
     */
    public function __construct(ServiceLocatorInterface $routeManager, array $routes)
    {
        $this->routeManager = $routeManager;
        $serial = 0;

        foreach ($routes as $name => $route) {
            if (!is_array($route)) {
                throw new Exception\UnexpectedValueException(sprintf(
                    'Route definition must be an array, %s given',
                    is_object($route) ? get_class($route) : gettype($route)
                ));
            }

            $this->routes[$name] = [
                'priority' => isset($route['priority']) ? $route['priority'] : 1,
                'serial'   => ++$serial,
                'options'  => $route,
                'instance' => null,
            ];
        }

        // Note: the order of the elements in the array is important for the sorting to work, do not change it!
        arsort($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!isset($this->routes[$name])) {
            throw new Exception\OutOfBoundsException(sprintf('Route with name "%s" was not found', $name));
        }

        $route = &$this->routes[$name];

        if (null === $route['instance']) {
            $route['instance'] = $this->routeManager->build(
                !isset($route['options']['type']) ? 'Generic' : $route['options']['type'],
                $route['options']
            );
        }

        return $route['instance'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->routes as $name => &$route) {
            if (null === $route['instance']) {
                $route['instance'] = $this->routeManager->build(
                    !isset($route['options']['type']) ? 'Generic' : $route['options']['type'],
                    $route['options']
                );
            }

            yield $name => $route['instance'];
        }
    }
}

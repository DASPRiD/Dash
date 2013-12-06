<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\RouteCollection;

use Dash\Router\Exception;
use Dash\Router\Http\Route\RouteInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Generic route collection which uses a service locator to instantiate routes.
 */
class RouteCollection implements RouteCollectionInterface
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
     * @var int
     */
    protected $serial = 0;

    /**
     * @var bool
     */
    protected $sorted = false;

    /**
     * @param ServiceLocatorInterface $routeBuilder
     */
    public function __construct(ServiceLocatorInterface $routeManager)
    {
        $this->routeManager = $routeManager;
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function insert($name, $route, $priority = 1)
    {
        if (!($route instanceof RouteInterface || is_array($route))) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$route must either be an array or implement Dash\Router\Http\Route\RouteInterface, %s given',
                is_object($route) ? get_class($route) : gettype($route)
            ));
        }

        $this->sorted = false;

        // Note: the order of the elements in the array are important for the
        // sorting to work, do not change!
        $this->routes[$name] = [
            'priority' => (int) $priority,
            'serial'   => $this->serial++,
            'route'    => $route,
        ];
    }

    public function remove($name)
    {
        if (!isset($this->routes[$name])) {
            return;
        }

        unset($this->routes[$name]);
    }

    public function clear()
    {
        $this->routes = [];
        $this->serial = 0;
        $this->sorted = true;
    }

    public function get($name)
    {
        if (!isset($this->routes[$name])) {
            throw new Exception\OutOfBoundsException(sprintf('Route with name "%s" was not found', $name));
        }

        $route = $this->routes[$name]['route'];

        if (!$route instanceof RouteInterface) {
            $type  = (!isset($route['type']) ? 'generic' : $route['type']);
            $route = $this->routes[$name]['route'] = $this->routeManager->get($type, $route);
        }

        return $route;
    }

    public function current()
    {
        $node = current($this->routes);
        return ($node !== false ? $this->get(key($this->routes)) : false);
    }

    public function key()
    {
        return key($this->routes);
    }

    public function next()
    {
        $node = next($this->routes);
        return ($node !== false ? $this->get(key($this->routes)) : false);
    }

    public function rewind()
    {
        if (!$this->sorted) {
            arsort($this->routes);
            $this->sorted = true;
        }

        reset($this->routes);
    }

    public function valid()
    {
        return ($this->current() !== false);
    }
}

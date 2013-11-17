<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\RouteCollection;

use Dash\Mvc\Router\Exception;
use Dash\Mvc\Router\Http\Route\RouteInterface;
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
     * @var int
     */
    protected $count = 0;

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

    public function insert($name, $route, $priority = 1)
    {
        if (!is_array($route) && !$route instanceof RouteInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '$route must either be an array or implement Dash\Mvc\Router\Http\Route\RouteInterface, %s given',
                is_object($route) ? get_class($route) : gettype($route)
            ));
        }

        $this->sorted = false;
        $this->count++;

        $this->routes[$name] = [
            'route'    => $route,
            'priority' => (int) $priority,
            'serial'   => $this->serial++,
        ];
    }

    public function remove($name)
    {
        if (!isset($this->routes[$name])) {
            return;
        }

        $this->count--;
        unset($this->routes[$name]);
    }

    public function clear()
    {
        $this->routes = [];
        $this->serial = 0;
        $this->count  = 0;
        $this->sorted = true;
    }

    public function get($name)
    {
        if (!isset($this->routes[$name])) {
            return null;
        }

        $route = $this->routes[$name]['route'];

        if (is_array($route)) {
            $type  = (!isset($route['type']) ? 'generic' : $route['type']);
            $route = $this->routes[$name]['route'] = $this->routeManager->get($type, $route);
        }


        return $route;
    }

    /**
     * Sort the route list.
     */
    protected function sort()
    {
        uasort($this->routes, [$this, 'compare']);
        $this->sorted = true;
    }

    /**
     * Compare the priority and serial of two routes.
     *
     * @param  array $route1,
     * @param  array $route2
     * @return int
     */
    protected function compare(array $route1, array $route2)
    {
        if ($route1['priority'] === $route2['priority']) {
            return ($route1['serial'] > $route2['serial'] ? -1 : 1);
        }

        return ($route1['priority'] > $route2['priority'] ? -1 : 1);
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
            $this->sort();
        }

        reset($this->routes);
    }

    public function valid()
    {
        return ($this->current() !== false);
    }

    public function count()
    {
        return $this->count;
    }
}

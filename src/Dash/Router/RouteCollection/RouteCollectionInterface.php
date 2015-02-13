<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\RouteCollection;

use Dash\Router\Exception;
use Dash\Router\Route\RouteInterface;
use Traversable;

/**
 * Interface every route collection must implement.
 */
interface RouteCollectionInterface extends Traversable
{
    /**
     * Inserts a new route into the list.
     *
     * The route may either be valid route object or a specifications for it.
     * If a route with the same name already exists, it will be replaced.
     *
     * When two routes shares the same priority, the last one inserted takes
     * precedence.
     *
     * @param string               $name
     * @param RouteInterface|array $route
     * @param int                  $priority
     */
    public function insert($name, $route, $priority = 1);

    /**
     * Removes a route from the list.
     *
     * @param string $name
     */
    public function remove($name);

    /**
     * Clears the entire list.
     */
    public function clear();

    /**
     * Gets a specific route.
     *
     * @param  string $name
     * @return RouteInterface
     * @throws Exception\OutOfBoundsException
     */
    public function get($name);
}

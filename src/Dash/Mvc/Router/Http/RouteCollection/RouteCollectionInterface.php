<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Mvc\Router\Http\RouteCollection;

use Countable;
use Dash\Mvc\Router\Http\Route\RouteInterface;
use Iterator;

/**
 * Interface every route collection must implement.
 */
interface RouteCollectionInterface extends Countable, Iterator
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
     * @return null|RouteInterface
     */
    public function get($name);
}

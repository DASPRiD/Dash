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
use Dash\Route\RouteInterface;
use Traversable;

/**
 * Interface every route collection must implement.
 */
interface RouteCollectionInterface extends Traversable
{
    /**
     * Gets a specific route.
     *
     * @param  string $name
     * @return RouteInterface
     * @throws Exception\OutOfBoundsException
     */
    public function get($name);
}

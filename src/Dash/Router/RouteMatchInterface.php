<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router;

/**
 * Interface describing matches which are returned by routers.
 */
interface RouteMatchInterface
{
    /**
     * Sets a single parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParam($name, $value);

    /**
     * Gets a specific parameter.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getParam($name, $default = null);

    /**
     * Gets all parameters.
     *
     * @return array
     */
    public function getParams();
}

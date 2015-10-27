<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Route;

use Dash\AbstractPluginManagerFactory;

/**
 * Factory for the route manager.
 */
class RouteManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * {@inheritdoc}
     */
    protected function getConfigKey()
    {
        return 'route_manager';
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassName()
    {
        return RouteManager::class;
    }
}

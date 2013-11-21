<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Route;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Default plugin manager for handling routes.
 */
class RouteManager extends AbstractPluginManager
{
    protected $shareByDefault = false;

    protected $factories = [
        'generic' => 'Dash\Router\Http\Route\GenericFactory',
    ];

    public function validatePlugin($plugin)
    {
        if ($plugin instanceof RouteInterface) {
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\RouteInterface',
            is_object($plugin) ? get_class($plugin) : gettype($plugin),
            __NAMESPACE__
        ));
    }
}
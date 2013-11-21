<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Parser;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Default plugin manager for handling parsers.
 */
class ParserManager extends AbstractPluginManager
{
    protected $shareByDefault = false;

    protected $factories = [
        'pathsegment'     => 'Dash\Router\Http\Parser\PathSegmentFactory',
        'hostnamesegment' => 'Dash\Router\Http\Parser\HostnameSegmentFactory',
    ];

    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ParserInterface) {
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\ParserInterface',
            is_object($plugin) ? get_class($plugin) : gettype($plugin),
            __NAMESPACE__
        ));
    }
}
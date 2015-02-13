<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Parser;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Default plugin manager for handling parsers.
 */
class ParserManager extends AbstractPluginManager
{
    protected $shareByDefault = false;

    protected $factories = [
        'pathsegment'     => Dash\Router\Parser\PathSegmentFactory::class,
        'hostnamesegment' => Dash\Router\Parser\HostnameSegmentFactory::class,
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
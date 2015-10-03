<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013-2015 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Parser;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for hostname segments.
 *
 * The factory creates a hostname-specific segment parser. Parsers which share
 * the same pattern and constraints will be cached and re-used.
 */
class HostnameSegmentFactory implements FactoryInterface
{
    /**
     * @var Segment[]
     */
    protected $cache = [];

    /**
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  array              $options
     * @return Segment
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $pattern     = (isset($options['hostname']) ? $options['hostname'] : '');
        $constraints = (isset($options['constraints']) ? $options['constraints'] : []);
        $key         = serialize([$pattern, $constraints]);

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = new Segment('.', $pattern, $constraints);
        }

        return $this->cache[$key];
    }
}

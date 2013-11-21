<?php
/**
 * Dash
 *
 * @link      http://github.com/DASPRiD/Dash For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Dash\Router\Http\Parser;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;

/**
 * Factory for hostname segments.
 *
 * The factory creates a hostname-specific segment parser. Parsers which share
 * the same pattern and constraints will be cached and re-used.
 */
class HostnameSegmentFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @var null|array
     */
    protected $createOptions;

    public function setCreationOptions(array $options)
    {
        $this->createOptions = $options;
    }

    /**
     * @return Segment
     */
    public function createService(ServiceLocatorInterface $parserManager)
    {
        $pattern     = (isset($this->createOptions['hostname']) ? $this->createOptions['hostname'] : '');
        $constraints = (isset($this->createOptions['constraints']) ? $this->createOptions['constraints'] : []);
        $key         = serialize([$pattern, $constraints]);

        if (!isset(static::$instances[$key])) {
            static::$instances[$key] = new Segment('.', $pattern, $constraints);
        }

        return static::$instances[$key];
    }
}